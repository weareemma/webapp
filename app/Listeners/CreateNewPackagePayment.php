<?php

namespace App\Listeners;

use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use App\Events\PackageChargeDone;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class CreateNewPackagePayment
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
   * @param  PackageChargeDone  $event
   * @return void
   */
  public function handle(PackageChargeDone $event)
  {
    try {
      DB::beginTransaction();

      $package = Package::find($event->data['packageData']['id']);
      User::buyPackage($event->user, $package);

      // Store payment
      Payment::storePayment(
        user: $event->user,
        subject: 'package',
        data: $event->data['chargeData'],
        payableType: Package::class,
        payableId: $package->id
      );

      DB::commit();
    } catch (\Throwable $th) {
      DB::rollBack();
      throw $th;
    }
  }
}
