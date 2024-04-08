<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Notifications\BookingReminderCustomer;
use App\Notifications\BookingReminderCustomerWhatsapp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class BookingReminderWhatsapp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try
        {
            $bookings = Booking::query()
                ->whereDate('date', Carbon::tomorrow())
                ->get();


            foreach ($bookings as $booking)
            {
                if ($booking->customer)
                {
                    $booking->customer->notify(new BookingReminderCustomerWhatsapp($booking));
                }
            }
        }
        catch (\Exception $ex)
        {
            Log::error('Job - Booking Reminder Whatsapp: ' . $ex->getMessage());
        }
    }
}
