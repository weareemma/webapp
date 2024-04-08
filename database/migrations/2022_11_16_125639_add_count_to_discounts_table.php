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
    Schema::table('discounts', function (Blueprint $table) {
      $table->json('counts')->after('maximum_count_per_user')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('discounts', function (Blueprint $table) {
      $table->dropColumn('counts');
    });
  }
};
