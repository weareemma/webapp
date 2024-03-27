<?php

namespace App\Listeners;

use App\Models\Booking;
use App\Models\Payment;
use App\Events\ChargeDone;
use App\Services\BookingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class CreateNewBookingPayment
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
   * @param  \App\Providers\ChargeDone  $event
   * @return void
   */
  public function handle(ChargeDone $event)
  {
    try {
      DB::beginTransaction();

      $payableId = null;
      if (empty($event->data['requestData']['additional_payload']['original_booking'])) {
        // Update booking if editing
        $payableId = $event->data['requestData']['additional_payload']['editing_id'] ?? null;
        if ($payableId) {
          $booking = Booking::find($payableId);
          BookingService::updateBooking($event->data['requestData']['additional_payload'], $booking);
          $subject = 'booking-edit';
        } else {
          $booking = BookingService::storeBooking($event->data['requestData']['additional_payload']);
          $payableId = $booking->id;
          $subject = 'booking-create';
        }
      } else {
        // find booking
        $payableId = $event->data['requestData']['additional_payload']['original_booking']['id'] ?? null;
        $booking = Booking::find($payableId);
        BookingService::updateBooking($event->data['requestData']['additional_payload'], $booking);
        $subject = 'booking-edit';
      }

      // Store payment
      Payment::storePayment(
        user: $event->user,
        subject: $subject,
        data: $event->data['chargeData'],
        payableType: Booking::class,
        payableId: $payableId
      );

      DB::commit();
    } catch (\Throwable $th) {
      DB::rollBack();
      throw $th;
    }
  }
}
