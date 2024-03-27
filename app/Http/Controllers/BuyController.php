<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Payment;
use App\Events\ChargeDone;
use App\Models\PlanPricing;
use Illuminate\Http\Request;
use App\Services\PlanService;
use App\Services\OrderService;
use Illuminate\Support\Carbon;
use App\Services\StripeService;
use App\Events\SubscriptionDone;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Events\PackageChargeDone;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Notifications\OrderCompleted;
use App\Services\ActiveCampaignService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Notifications\Admin\BookingConfirmation;
use App\Notifications\SubscriptionUpdatedCustomer;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Admin\SubscriptionConfirmation;
use App\Notifications\AppointmentConfirmationCustomer;
use App\Notifications\SubscriptionConfirmationCustomer;
use Illuminate\Support\Arr;

class BuyController extends Controller
{
  /**
   * Subscriptions page
   *
   * @return \Inertia\Response
   */
  public function subscription(Request $request)
  {
    $plans = Plan::query()->orderBy('created_at')->with('pricings')->get();
    return Inertia::render('Buy/Subscriptions', [
      'plans' => $plans,
      'swap' => boolval($request->swap) ?? false
    ]);
  }

  /**
   * Swap subscription
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  public function swapSubscription(Request $request)
  {
    $user = Auth::user();
    $current_subscription = $user->subscriptions()->active()->first();
    $user->subscription($current_subscription->name)->noProrate()->cancelNow();
//    $pricing = PlanPricing::find($request->post('price_id'));
//    $subscription = $user->getFirstSubscription();
//    $subscription->swapAndInvoice($pricing->stripe_price_id);
//    $subscription->name = $pricing->plan->stripe_product_id;
//    $subscription->save();
//
//    $user->notify(new SubscriptionUpdatedCustomer());

    return Redirect::back();
  }

  public function package()
  {
    $packages = Package::query()->orderBy('created_at')->get();
    return Inertia::render('Buy/Packages', [
      'packages' => $packages
    ]);
  }

  /**
   * Subscriptions checkout
   *
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse|\Inertia\Response
   */
  public function subscriptionCheckout(Plan $plan)
  {
    if ( ! $plan->active)
    {
        return Redirect::back();
    }

    $user = Auth::user();
    return Inertia::render('Buy/Checkout', [
      'user' => $user,
      'intent' => $user->createSetupIntent(),
      'stripeKey' => config('cashier.key'),
      'checkoutType' => 'subscription',
      'model' => $plan,
      'totalAmount' => null,
      'prices' => $plan->pricings()->with('plan')->get()
    ]);
  }

  /**
   * Package checkout
   *
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse|\Inertia\Response
   */
  public function packageCheckout(Package $package)
  {
    $user = Auth::user();
    return Inertia::render('Buy/Checkout', [
      'user' => $user,
      'intent' => $user->createSetupIntent(),
      'stripeKey' => config('cashier.key'),
      'checkoutType' => 'package',
      'model' => $package,
      'totalAmount' => $package->price,
    ]);
  }

  /**
   * Performe a payment
   *
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
   */
  public function buy(Request $request)
  {
      try
      {
          $user = Auth::user();
          $total = $request->total;
          $type = $request->type;

          // Stripe customer creation
          $user->createOrGetStripeCustomer();

          // Temporary stripe ref code
          $ref = PaymentService::createUniqueIdentifier($user);

          // Session data
          $session_data = [
              'type' => $type,
              'total' => $total,
              'payload' => $request->additional_payload,
              'ref' => $ref
          ];

          switch ($type) {
              case 'charge':
                  // Create or update order status
                  $original_booking = Booking::find(
                    $request->additional_payload['editing_id'] ?? null
                  );
                  if ($original_booking)
                  {
                    $subject = 'booking-edit';
                    $order = $original_booking->order;
                    $order->update([
                        'status' => Order::STATUS_UPDATING,
                        'data' => json_encode(Arr::except($request->all(), 'additional_payload.available_days'))
                    ]);
                  }
                  else
                  {
                    $subject = 'booking-create';
                    // $order = Order::create([
                    //     'user_id' => $user->id,
                    //     'total' => $total,
                    //     'status' => Order::STATUS_PENDING,
                    //     'data' => json_encode(Arr::except($request->all(), 'additional_payload.available_days'))
                    // ]);
                    $order = new Order();
                    $order->user_id = $user->id;
                    $order->total = $total;
                    $order->status = Order::STATUS_PENDING;
                    $order->data = Arr::except($request->all(), 'additional_payload.available_days');
                    $order->save();
                  }

                  $url = $user->checkoutCharge($total * 100, 'Appuntamento', 1, [
                      'success_url' => route('buy.charge.after'),
                      'cancel_url' => route('booking.wizard'),
                      'invoice_creation' => [
                          'enabled' => true
                      ],
                      'payment_intent_data' => [
                          'metadata' => [
                              'ref' => $ref,
                              'order_id' => $order->id,
                              'subject' => $subject,
                              'type' => 'charge'
                          ],
                          'setup_future_usage' => 'off_session'
                      ],
                      'tax_id_collection' => ['enabled' => true],
                      'expires_at' => Carbon::now()->addSeconds(config('app.checkout_session_timeout', 60 * 30))->timestamp
                  ]);

                  if (is_null($url))
                  {
                      throw new \Exception("No url provided from Stripe");
                  }

                  // Store session data
                  $session_data['request'] = $request->all();
                  $session_data['request']['additional_payload']['order_id'] = $order->id;
                  $session_data['request']['additional_payload']['ref'] = $ref;
                  Session::put('checkout', $session_data);

                  return Inertia::location($url->url);

              case 'subscription':
                  $product = $request->additional_payload['plan']['stripe_product_id'];
                  $price = $request->additional_payload['stripe_price_id'];
                  $plan = PlanPricing::query()
                      ->with('plan')
                      ->where('stripe_price_id', $price)->first();

                  // If is promo sub, create a charge checkout instead of a sub checkout
                  if ($plan->plan->name ==  config('app.promo_name'))
                  {
                      $session_data['promo'] = true;
                      $total = $plan->price;

                      $url = $user->checkoutCharge($total * 100, 'Abbonamento Promo', 1, [
                          'success_url' => route('buy.subscription.after'),
                          'cancel_url' => route('buy.subscription.checkout', $plan->plan),
                          'invoice_creation' => [
                              'enabled' => true
                          ],
                      ]);
                  }
                  else
                  {
                      $session_data['promo'] = false;

                      $url = $user->newSubscription(
                          $product,
                          $price
                      )
                          ->checkout([
                              'success_url' => route('buy.subscription.after'),
                              'cancel_url' => route('buy.subscription.checkout', $plan->plan),
                              'payment_method_types' => [
                                  'card'
                              ]
                          ]);
                  }


                  if (is_null($url)) {
                      throw new \Exception("No url provided from Stripe");
                  }

                  // Store session data
                  $session_data['plan'] = $plan;
                  Session::put('checkout', $session_data);

                  return Inertia::location($url->url);

              case 'package':
                  $url = $user->checkoutCharge($total * 100, 'Pacchetto', 1, [
                      'success_url' => route('buy.package.after'),
                      'cancel_url' => route('buy.package.checkout', $request->additional_payload['id']),
                      'invoice_creation' => [
                          'enabled' => true
                      ],
                      'payment_intent_data' => [
                          'setup_future_usage' => 'off_session'
                      ],
                  ]);

                  if (is_null($url))
                  {
                      throw new \Exception("No url provided from Stripe");
                  }

                  // Store session data
                  $session_data['request'] = $request->all();
                  Session::put('checkout', $session_data);

                  return Inertia::location($url->url);

              default:
                  Log::error('Buy: No type found.');
                  return Redirect::route('buy.cancel');
                  break;
          }
      } catch (\Exception $ex) {
          Log::error('Stripe fail: ' . $ex->getMessage());
          return Redirect::route('buy.cancel');
      }
  }

    /**
     * After checkout subscription flow
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
  public function buyAfterSubscription(Request $request)
  {
      try
      {
          // Check if user is subscribed
          $user = Auth::user();
          $session_data = Session::get('checkout');
          Session::forget('checkout');

          if ($session_data['promo'])
          {
              $user->newSubscription(
                  $session_data['plan']['plan']['stripe_product_id'],
                  $session_data['plan']['stripe_price_id']
              )->add([],[
                  'cancel_at' => PlanService::getPromoCancelAtTimestamp(),
                  'collection_method' => 'send_invoice',
                  'days_until_due' => 100
              ]);

              StripeService::disableAutoAdvanceAllDraftInvoices($user);
          }

          // Notify user
          $user->notify(new SubscriptionConfirmationCustomer());

          // Notify completed order
          $user->notify(new OrderCompleted(
              "Abbonamento " . $session_data['plan']['plan']['name'],
              $session_data['total']
          ));

          // Notify info
          $info = new User(['email' => 'info@weareemma.com']);
          $info->notify(new SubscriptionConfirmation([
              'customer' => $user->full_name ?? '',
              'plan' => $session_data['plan']['plan']['name']
          ]));

          // Set default payment method
          $user->updatePaymentMethod();

          return Redirect::route('buy.success')->with(['flash_data' => [
              'subscription' => $session_data['payload']
          ]]);
      }
      catch (\Exception $ex)
      {
          Log::error('Buy after subscription: ' . $ex->getMessage());
          return Redirect::route('buy.subscription.checkout', $session_data['plan']['plan']['id']);
      }
  }

  public function buyAfterCharge(Request $request)
  {
      try
      {
          $user = Auth::user();
          $session_data = Session::get('checkout');
          Session::forget('checkout');

          $booking = BookingService::upsertBookingAfterCharge(
              user: $user,
              data: [
                  'chargeData' => [
                      'id' => '',
                      'metadata' => [
                          'type' => 'charge'
                      ],
                      'amount' => $session_data['total'] * 100,
                      'ref' => $session_data['ref']
                  ],
                  'requestData' => $session_data['request'],
              ]
          );

          // Set default payment method
          $user->updatePaymentMethod();

          // Update order status
          $booking->order->update([
            'status' => Order::STATUS_COMPLETED
          ]);

          return Redirect::route('buy.success')->with(['flash_data' => [
              'booking' => $booking ? $booking?->load('store') : null
          ]]);
      }
      catch (\Exception $ex)
      {
          Log::error('Buy after charge: ' . $ex->getMessage());
          return Redirect::route('booking.wizard');
      }
  }

  public function buyAfterPackage(Request $request)
  {
      try
      {
          $user = Auth::user();
          $session_data = Session::get('checkout');
          Session::forget('checkout');

          PackageChargeDone::dispatch($user, [
              'chargeData' => [
                  'id' => '',
                  'metadata' => [
                      'type' => 'package'
                  ],
                  'amount' => $session_data['total'] * 100
              ],
              'packageData' => $session_data['payload'],
          ]);

          // Set default payment method
          $user->updatePaymentMethod();

          // Notify completed order
          $user->notify(new OrderCompleted(
              "Pacchetto " . $session_data['payload']['name'],
              $session_data['total']
          ));

          return Redirect::route('buy.success')->with(['flash_data' => [
              'pack' => $session_data['payload']
          ]]);
      }
      catch (\Exception $ex)
      {
          Log::error('Buy after package: ' . $ex->getMessage() . ' ' . $ex->getFile() . ' ' . $ex->getLine());
          return Redirect::route('buy.package.checkout', $session_data['payload']['id']);
      }
  }


  /**
   * Success page
   *
   * @return \Inertia\Response
   */
  public function success()
  {
    return Inertia::render('Buy/Success', []);
  }

  /**
   * Cancel page
   *
   * @return \Inertia\Response
   */
  public function cancel()
  {
    return Inertia::render('Buy/Cancel', []);
  }

  /**
   * Subscribe to newsletter
   *
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function newsletter(Request $request)
  {
      $request->validate([
          'name' => 'required|string',
          'email' => 'required|email',
          'policy' => 'accepted'
      ]);

      $user = new User([
          'name' => $request->post('name'),
          'surname' => '',
          'email' => $request->post('email')
      ]);

      ActiveCampaignService::addNewsletterTag($user);

      return Redirect::back()->with('success', __("Sei iscritto alla newsletter!"));
  }

  public function test()
  {
    $user = User::find(1);
    $invoice = $user->invoices()->filter(function ($invoice) {
      return $invoice->payment_intent == 'pi_3LUxu5G01DVfHAQR1V5diKmW';
    })->first();
    //        dd($invoice->payment_intent);
    //        dd($user->invoices()->filter(function ($invoice) {
    //            return $invoice->payment_intent == 'pi_3LUxu5G01DVfHAQR1V5diKmW';
    //        })->first());

    dd($invoice);

    return $user->downloadInvoice($invoice->id, [
      'vendor' => 'Your Company',
      'product' => 'Your Product',
    ]);
  }
}
