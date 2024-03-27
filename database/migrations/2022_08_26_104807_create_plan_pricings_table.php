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
    Schema::create('plan_pricings', function (Blueprint $table) {
      $table->id();
      $table->foreignId('plan_id')->constrained()->onDelete('cascade');
      $table->string('stripe_price_id');
      $table->string('duration_type');
      $table->unsignedTinyInteger('duration_qty');
      $table->decimal('price', 10);
      $table->boolean('active');
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('plan_pricings');
  }
};
