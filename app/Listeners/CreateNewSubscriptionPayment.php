<?php

namespace App\Listeners;

use App\Events\SubscriptionDone;
use App\Models\Payment;
use App\Models\PlanPricing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class CreateNewSubscriptionPayment
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
   * @param  \App\Events\SubscriptionDone  $event
   * @return void
   */
  public function handle(SubscriptionDone $event)
  {
    try {
      DB::beginTransaction();

      $pricing = PlanPricing::find($event->data['pricingData']['id']);

      // Store payment
      Payment::storePayment(
        user: $event->user,
        subject: 'subscription',
        data: $event->data['subscriptionData'],
        payableType: PlanPricing::class,
        payableId: $pricing->id
      );

      DB::commit();
    } catch (\Throwable $th) {
      DB::rollBack();
      throw $th;
    }
  }
}
