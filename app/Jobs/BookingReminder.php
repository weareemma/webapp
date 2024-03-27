<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Notifications\BookingReminderCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class BookingReminder implements ShouldQueue
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

            $start = $this->takeFirstQuarterOfHour();

            $bookings = Booking::query()
                ->whereDate('date', Carbon::now()->addDays(2))
                ->where('start', $start)
                ->get();


            foreach ($bookings as $booking)
            {
                if ($booking->customer)
                {
                    $booking->customer->notify(new BookingReminderCustomer($booking));
                }
            }
        }
        catch (\Exception $ex)
        {
            Log::error('Job - Booking Reminder : ' . $ex->getMessage());
        }
    }

    /**
     * Take first quarter of hour
     *
     * @return string
     */
    private function takeFirstQuarterOfHour()
    {
        $start = now()->startOfHour();
        $diff = $start->diffInMinutes(now());
        return $start->addMinutes(15 * intval($diff/15))->format('H:i:s');
    }
}
