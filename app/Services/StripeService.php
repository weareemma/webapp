<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Stripe\Invoice;
use Stripe\StripeClient;

class StripeService
{
  /**
   * Setup stripe api connection
   *
   * @return StripeClient
   */
  public static function setupStripe()
  {
    return new StripeClient(
      config('cashier.secret')
    );
  }

  /**
   * Create stripe product
   *
   * @param $data
   * @param $stripe_connection
   * @return mixed|null
   * @throws \Stripe\Exception\ApiErrorException
   */
  public static function createProduct($data = [], $stripe_connection = null)
  {
    $stripe = $stripe_connection ?? self::setupStripe();
    return self::parseResponse($stripe->products->create(self::prepareData($data)));
  }

  /**
   * Update a stripe product
   *
   * @param $stripe_product_id
   * @param $data
   * @param $stripe_connection
   * @return mixed|null
   * @throws \Stripe\Exception\ApiErrorException
   */
  public static function updateProduct($stripe_product_id, $data = [], $stripe_connection = null)
  {
    $stripe = $stripe_connection ?? self::setupStripe();
    return self::parseResponse($stripe->products->update($stripe_product_id, self::prepareData($data)));
  }

  /**
   * Delete stripe product
   *
   * @param $stripe_product_id
   * @param $stripe_connection
   * @return mixed|null
   * @throws \Stripe\Exception\ApiErrorException
   */
  public static function deleteProduct($stripe_product_id, $stripe_connection = null)
  {
    $stripe = $stripe_connection ?? self::setupStripe();
    return self::parseResponse($stripe->products->delete($stripe_product_id));
  }

  /**
   * Create stripe price
   *
   * @param $data
   * @param $stripe_connection
   * @return mixed|null
   * @throws \Stripe\Exception\ApiErrorException
   */
  public static function createPrice($data = [], $stripe_connection = null)
  {
    $stripe = $stripe_connection ?? self::setupStripe();
    return self::parseResponse($stripe->prices->create(self::prepareData($data)));
  }

  /**
   * Update stripe price
   *
   * @param $stripe_price_id
   * @param $data
   * @param $stripe_connection
   * @return mixed|null
   * @throws \Stripe\Exception\ApiErrorException
   */
  public static function updatePrice($stripe_price_id, $data = [], $stripe_connection = null)
  {
    $stripe = $stripe_connection ?? self::setupStripe();
    return self::parseResponse($stripe->prices->update($stripe_price_id, self::prepareData($data)));
  }

  /**
   * Convert price for stripe
   *
   * @param $price
   * @return float
   */
  public static function convertStripePrice($price)
  {
    return round($price * 100, 0);
  }

  /**
   * Build user invoice download link
   *
   * @param User|null $user
   * @param Payment|null $payment
   * @return \Symfony\Component\HttpFoundation\Response|void
   */
  public static function getInvoiceDownloadLink(User $user = null, Payment $payment = null)
  {
    if ($user && $payment) {
      $invoice = $user->invoices()->filter(function ($invoice) use ($payment) {
        return $invoice->payment_intent == $payment->stripe_payment_id ?? '';
      })->first();

      if ($invoice) {
        return $user->downloadInvoice($invoice->id, [
          'vendor' => config('app.name'),
          'product' => $payment->subject ?? '-',
        ]);
      }
    }

    return null;
  }


  /**
   * Parse stripe response
   *
   * @param $response
   * @return mixed|null
   */
  private static function parseResponse($response = null)
  {
    return $response->toArray();
  }

  /**
   * Prepare data for stripe
   *
   * @param $data
   * @return array|mixed
   */
  private static function prepareData($data = [])
  {
    if (array_key_exists('description', $data)) {
      if (is_null($data['description'])) unset($data['description']);
    }
    return $data;
  }

  /**
   * Update user payment method
   *
   * @param User $user
   * @param $payment_method_id
   * @return void
   */
  public static function updateDefaultPaymentMethod(User $user, $payment_method_id)
  {
      $user->updateDefaultPaymentMethod($payment_method_id);
  }

  /**
   * Disabled auto advance on draft invoices
   *
   * @param User $user
   * @return void
   * @throws \Stripe\Exception\ApiErrorException
   */
  public static function disableAutoAdvanceAllDraftInvoices(User $user = null)
  {
      if ($user && $user->stripe_id)
      {
          $stripe = $stripe_connection ?? self::setupStripe();
          $invoices = $stripe->invoices->all([
              'customer' => $user->stripe_id,
              'status' => Invoice::STATUS_DRAFT
          ]);

          foreach ($invoices as $invoice)
          {
              $stripe->invoices->update(
                  $invoice->id,
                  [
                      'auto_advance' => false
                  ]
              );
          }
      }
  }

    /**
     * Set subscription billing cycle to now
     *
     * @param Subscription|null $subscription
     * @return void
     * @throws \Stripe\Exception\ApiErrorException
     */
    public static function setSubscriptionBillingCycleToNow(Subscription $subscription = null)
    {
        if ($subscription)
        {
            $stripe = $stripe_connection ?? self::setupStripe();
            $stripe->subscriptions->update(
                $subscription->stripe_id,
                [
                    'billing_cycle_anchor' => 'now',
                    'pause_collection' => [
                        'behavior' => 'keep_as_draft',
                        'resumes_at' => Carbon::now()->timestamp
                    ]
                ]
            );
        }
    }

}
