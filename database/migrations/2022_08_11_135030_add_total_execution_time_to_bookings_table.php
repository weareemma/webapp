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
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('total_execution_time', 10, 2)->after('slots');
            $table->time('start')->after('slots');
            $table->decimal('total_net_price_original', 10, 2)->after('total_net_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('total_execution_time');
            $table->dropColumn('start');
            $table->dropColumn('total_net_price_original');
        });
    }
};
