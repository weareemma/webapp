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
            $table->dateTime('stylist_started_at')->nullable()->after('status');
            $table->dateTime('stylist_ended_at')->nullable()->after('stylist_started_at');
            $table->text('stylist_notes')->nullable()->after('stylist_ended_at');
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
            $table->dropColumn('stylist_started_at');
            $table->dropColumn('stylist_ended_at');
            $table->dropColumn('stylist_notes');
        });
    }
};
