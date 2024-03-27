<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\Order;
use App\Models\Package;
use App\Models\Payment;
use Stripe\InvoiceItem;
use Stripe\StripeClient;
use App\Models\Subscription;
use App\Services\PlanService;
use Illuminate\Support\Carbon;
use App\Events\SubscriptionDone;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\IpraticoService;
use Illuminate\Support\Facades\Log;
use App\Events\SubscriptionCanceled;
use Illuminate\Support\Facades\Auth;
use App\Notifications\OrderCompleted;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Session;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Cashier\Events\WebhookReceived;
use App\Notifications\Admin\BookingConfirmation;
use App\Notifications\AppointmentConfirmationCustomer;

class StripeEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(WebhookReceived $event)
    {
        $stripeDataObject = $event->payload['data']['object'];

        try
        {
            if ($event->payload['type'] === 'payment_intent.succeeded')
            {

                // Link stripe payment with emma payment
                if (
                    array_key_exists('ref', $stripeDataObject['metadata'])
                )
                {
                    Log::info($stripeDataObject['id'] . ' <-> ' .$stripeDataObject['metadata']['ref']);
                    PaymentService::setStripePaymentId(
                        $stripeDataObject['id'],
                        $stripeDataObject['metadata']['ref'],
                        $stripeDataObject
                    );
                }
            }

            // Create refund
            if ($event->payload['type'] === 'charge.refunded')
            {
                PaymentService::createRefund($stripeDataObject['metadata']['ref']);
            }

            if ($event->payload['type'] === 'invoice.payment_succeeded')
            {
                // Create sub order on iPratico
                if (in_array($stripeDataObject['billing_reason'], [
                    'subscription_cycle',
                    'subscription_update',
                    'subscription_create'
                ]))
                {
                    $customer_id = $stripeDataObject['customer'];
                    $user = User::query()->where('stripe_id', $customer_id)->first();

                    if ($user)
                    {
                        (new IpraticoService(
                            config('services.ipratico.api_subscription_key')
                        ))->createSubscriptionOrder($user);
                    }

                    // Create payment
                    if (isset($stripeDataObject['subscription']))
                    {
                        $subscription = Subscription::query()
                            ->where('stripe_id', $stripeDataObject['subscription'])
                            ->first();

                        PaymentService::createPaymentForSubscription(
                            $subscription,
                            $stripeDataObject['payment_intent'],
                            str_replace('_', '-', $stripeDataObject['billing_reason'])
                        );

                        if ($stripeDataObject['billing_reason'] == 'subscription_cycle')
                        {
                            $end_period = $stripeDataObject['lines']['data'][0]['period']['end'] ?? null;

                            if ($end_period)
                            {
                                SubscriptionService::createSubDiscount(
                                    $user,
                                    now(),
                                    Carbon::parse($end_period)
                                );
                            }
                        }
                    }
                }
            }

            if ($event->payload['type'] === 'customer.subscription.created')
            {
                $customer_id = $stripeDataObject['customer'];
                $user = User::query()->where('stripe_id', $customer_id)->first();

                if ($user)
                {
                    if ($stripeDataObject['current_period_end'])
                    {
                        SubscriptionService::createSubDiscount(
                            $user,
                            now(),
                            Carbon::parse($stripeDataObject['current_period_end'])
                        );
                    }
                }
            }

            if ($event->payload['type'] === 'customer.subscription.deleted')
            {
                $subscription = Subscription::query()
                    ->where('stripe_id', $stripeDataObject['id'])
                    ->first();

                if ($subscription)
                {
                    SubscriptionCanceled::dispatch($subscription);
                }
            }

            if ($event->payload['type'] === 'invoiceitem.created')
            {
                if ($stripeDataObject['proration'])
                {
                    $stripe = new StripeClient(
                        config('cashier.secret')
                    );
                    // Delete invoice item
                    $stripe->invoiceItems->delete($stripeDataObject['id']);
                }
            }
        }
        catch (\Exception $ex)
        {
            Log::error('Stripe listener: ' . $ex->getMessage());
        }
    }
}
