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
            $table->boolean('different_services')->default(false)->nullable()->after('start');
            $table->boolean('multiple')->default(false)->nullable()->after('different_services');
            $table->unsignedBigInteger('parent_id')->nullable()->after('ipratico_id');

            $table->foreign('parent_id', 'parent_bookings')->references('id')->on('bookings')->onDelete('cascade');
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
            $table->dropColumn('different_services');
            $table->dropColumn('multiple');
            $table->dropColumn('parent_id');
        });
    }
};
