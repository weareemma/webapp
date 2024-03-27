<?php

namespace App\Services;

use Carbon\Carbon;
use Stripe\Stripe;
use App\Models\User;
use App\Models\Order;
use App\Models\Refund;
use App\Models\Payment;
use Stripe\PaymentIntent;
use App\Models\PlanPricing;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Create temporary ref code between stripe and emma
     *
     * @param User $user
     * @return string
     */
    public static function createUniqueIdentifier(User $user)
    {
        return $user->id . '_' . now()->format('YmdHis');
    }

    /**
     * Set stripe payment id into Emma payment
     *
     * @param $payment_intent
     * @param $stripe_ref
     * @return void
     */
    public static function setStripePaymentId($payment_intent, $stripe_ref, $stripe_object)
    {
        $user = User::query()
                ->where('stripe_id', $stripe_object['customer'])
                ->first();

        Payment::create([
            'user_id' => $user->id,
            'date' => Carbon::now(),
            'total' => round(intval($stripe_object['amount']) / 100, 2),
            'stripe_payment_id' => $payment_intent,
            'stripe_ref' => $stripe_ref,
            'method' => Payment::PROVIDER_STRIPE,
            'subject' => $stripe_object['metadata']['subject'] ?? null,
            'payable_type' => Order::class,
            'payable_id' => $stripe_object['metadata']['order_id'] ?? null,
            'type' => $stripe_object['metadata']['type'] ?? null,
        ]);
    }

    /**
     * Get stripe payment id by ref
     *
     * @param User $user
     * @param $stripe_ref
     * @return mixed|null
     * @throws \Stripe\Exception\ApiErrorException
     */
    public static function getStripePaymentIdByRef(User $user, $stripe_ref)
    {
        Stripe::setApiKey(config('cashier.secret'));
        $found = PaymentIntent::search([
            'query' => "metadata['ref']:'". $stripe_ref ."'"
        ])->first();

        if (! $found)
        {
            Log::error("Payment service: " . $stripe_ref . ' not found!');
        }

        return ($found)
            ? $found['id']
            : null;
    }

    /**
     * Create payment fro sub
     * 
     */
    public static function createPaymentForSubscription(Subscription $subscription = null, $stripe_payment_id = null, $subject = null)
    {
        if ($subscription)
        {
            $plan_price = PlanPricing::query()
                ->where('stripe_price_id', $subscription->stripe_price)
                ->first();

            if ($plan_price)
            {
                Payment::create([
                    'user_id' => $subscription->user->id,
                    'type' => 'subscription',
                    'subject' => $subject ?? '',
                    'date' => now(),
                    'total' => $plan_price->price,
                    'method' => 'stripe',
                    'stripe_payment_id' => $stripe_payment_id,
                    'stripe_ref' => null,
                    'refunded' => false,
                    'payable_id' => $plan_price->id,
                    'payable_type' => PlanPricing::class
                ]);
            }

        }
    }

    /**
     * Create refund
     * 
     */
    public static function createRefund($stripe_ref = null)
    {
        $payment = Payment::query()
            ->with(['user', 'payable'])
            ->where('stripe_ref', $stripe_ref)
            ->first();
        
        if ($payment)
        {
            $payment->update([
                'refunded' => true
            ]);

            // Create refund record
            Refund::create([
                'user_id' => $payment->user->id,
                'total' => $payment->total,
                'stripe_payment_id' => $payment->stripe_payment_id,
                'refundable_id' => $payment->payable->id,
                'refundable_type' => $payment->payable_type
            ]);
        }
    }
}