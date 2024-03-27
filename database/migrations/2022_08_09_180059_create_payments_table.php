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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type')->nullable();
            $table->string('subject')->nullable();
            $table->dateTime('date')->nullable()->default(now());
            $table->double('total', 8, 2)->nullable();
            $table->string('method')->nullable();
            $table->string('provider')->nullable();
            $table->string('stripe_payment_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'payment_user')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
