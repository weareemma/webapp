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
        Schema::table('hair_services', function (Blueprint $table) {
            $table->string('ipratico_id')->nullable()->after('id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('ipratico_id')->nullable()->after('id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('ipratico_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hair_services', function (Blueprint $table) {
            $table->dropColumn('ipratico_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('ipratico_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ipratico_id');
        });
    }
};
