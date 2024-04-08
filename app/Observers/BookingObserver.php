<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\IpraticoService;
use Illuminate\Support\Facades\Log;

class BookingObserver
{
    public $afterCommit = true;
    
    /**
     * Handle the Booking "created" event.
     *
     * @param Booking $booking
     * @return void
     */
    public function created(Booking $booking)
    {
        // Create order in Ipratico
        try
        {
            (new IpraticoService($booking->store->ipratico_key))->createOrUpdateOrder($booking);
        }
        catch (\Exception $ex)
        {
            Log::error('Booking Created: ' . $ex->getMessage() . ' Line:' . $ex->getLine() . ' FIle: ' . $ex->getFile());
        }
    }

    /**
     * Handle the Booking "updated" event.
     *
     * @param Booking $booking
     * @return void
     */
    public function updated(Booking $booking)
    {
        try
        {
            if (
                $booking->ipratico_id &&
                $booking->wasChanged([
                    'date',
                    'start',
                    'status',
                    'store_id',
                    'total_execution_time',
                    'total_net_price'
                ])
            )
            {
                (new IpraticoService($booking->store->ipratico_key))->createOrUpdateOrder($booking);
            }
            
            
        }
        catch (\Exception $ex)
        {
            Log::error('Booking Updated: ' . $ex->getMessage());
        }
    }

    /**
     * Handle the Booking "deleted" event.
     *
     * @param Booking $booking
     * @return void
     */
    public function deleted(Booking $booking)
    {
        try
        {
            (new IpraticoService($booking->store->ipratico_key))->cancelOrder($booking);
        }
        catch (\Exception $ex)
        {
            Log::error('Booking Deleted: ' . $ex->getMessage());
        }
    }

    /**
     * Handle the Booking "restored" event.
     *
     * @param Booking $booking
     * @return void
     */
    public function restored(Booking $booking)
    {
        //
    }

    /**
     * Handle the Booking "force deleted" event.
     *
     * @param Booking $booking
     * @return void
     */
    public function forceDeleted(Booking $booking)
    {
        //
    }
}
