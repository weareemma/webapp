<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\ActivityService;
use App\Services\BookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Facades\LogBatch;

class BookingCheckBalance implements ShouldQueue
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
            LogBatch::startBatch();

            $today_bookings = Booking::query()
                ->whereDate('date', now())
                ->get();

            ActivityService::logActivityForJob(
                self::class,
                'start',
                $today_bookings->count() . ' appuntamenti da elaborare'
            );

            foreach ($today_bookings as $booking)
            {
                $res = BookingService::bookingCheckBalance($booking);

                $logMessages = [
                    3 => 'Scartato per data nel passato',
                    4 => 'Scartato poichè non padre',
                    2 => 'Utente non più abbonato, deve pagare',
                    1 => 'Utente abbonato, riceve uno sconto'
                ];

                $log = $logMessages[$res] ?? null;

                if ($log)
                {
                    ActivityService::logActivityForJob(
                        self::class,
                        'running on',
                        'Booking ' . $booking->id . ': ' . $log
                    );
                }
            }

            ActivityService::logActivityForJob(
                self::class,
                'end',
                'Fine esecuzione'
            );

            LogBatch::endBatch();
        }
        catch (\Exception $ex)
        {
            ActivityService::logActivityForJob(
                self::class,
                'error',
                $ex->getMessage()
            );
            LogBatch::endBatch();
            Log::error('BookingCheckBalance Job: ' . $ex->getMessage());
            activity()
                ->causedByAnonymous()
                ->event(self::class . ' End with errors')
                ->log('Error: ' . $ex->getMessage());
            LogBatch::endBatch();
        }
    }
}
