<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('payments', function (Blueprint $table) {
      $table->unsignedInteger("payable_id")->after('stripe_payment_id');
      $table->string("payable_type")->after('payable_id');
      $table->index(["payable_id", "payable_type"]);
      $table->dropColumn('booking_id');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('payments', function (Blueprint $table) {
      $table->foreignId('booking_id')->after('stripe_payment_id')->nullable();
      $table->dropMorphs('payable');
    });
  }
};
