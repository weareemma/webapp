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
            $table->dateTime('date')->nullable()->change();
            $table->unsignedInteger("payable_id")->nullable()->change();
            $table->string("payable_type")->nullable()->change();
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
            $table->unsignedInteger("payable_id")->nullable(false)->change();
            $table->string("payable_type")->nullable(false)->change();
        });
    }
};
