<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateBalance
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

            foreach ($bookings as $booking)
            {
                $booking->addPrimaryServicePrice();
                $booking->save();
            }
        }
    }
}
