<?php

namespace App\Jobs;

use Exception;
use App\Models\Order;
use App\Models\CheckoutError;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class CheckForCheckoutError implements ShouldQueue
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
            $orders = Order::query()
                ->whereHas('payments')
                ->where('status', Order::STATUS_PENDING)
                ->whereDate('created_at', '>', now()->subDays(7))
                ->get();

            foreach ($orders as $order)
            {
                $already_exist = CheckoutError::query()->where('order_id', $order->id)->first();

                if (is_null($already_exist))
                {
                    $data = $order->data;

                    if (
                        isset($data['additional_payload']['selected_day']['date']) &&
                        isset($data['additional_payload']['selected_slot']['time'])
                    )
                    {
                        $booking_for = Carbon::parse($data['additional_payload']['selected_day']['date'])
                            ->setTimeFromTimeString($data['additional_payload']['selected_slot']['time']);

                        $last_payment = $order->payments->first();

                        CheckoutError::create([
                            'user_id' => $order->user_id,
                            'store_id' => $data['additional_payload']['store_id'],
                            'order_id' => $order->id,
                            'booking_for' => $booking_for,
                            'paid_at' => $last_payment->created_at,
                            'total' => $data['total'],
                            'resolved' => false
                        ]);
                    }
                }
            }
        }
        catch (Exception $ex)
        {
            Log::error('Check for checkout error job: ' . $ex->getMessage());
        }
    }
}
