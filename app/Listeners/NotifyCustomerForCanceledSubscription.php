<?php

namespace App\Listeners;

use App\Notifications\SubscriptionCanceledCustomer;
use App\Notifications\UpcomingBookings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyCustomerForCanceledSubscription
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
    public function handle($event)
    {
        $subscription = $event->subscription;

        if ($subscription && $subscription->user)
        {
            // Find all upcoming bookings
            $bookings = $subscription
                ->user
                ->bookings()
                ->upcoming()
                ->get();

            if ($bookings->count() > 0)
            {
                $subscription->user->notify(
                    new SubscriptionCanceledCustomer($bookings)
                );
            }
        }
    }
}
