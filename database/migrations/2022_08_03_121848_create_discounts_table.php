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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('checkout_type')->nullable();
            $table->string('typology')->nullable();
            $table->double('percentage', 5, 2)->nullable();
            $table->double('value', 5, 2)->nullable();
            $table->double('minimum_charge', 5, 2)->nullable();
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_to')->nullable();
            $table->bigInteger('maximum_count_per_user')->nullable();
            $table->json('stores')->nullable();
            $table->json('users')->nullable();
            $table->json('services')->nullable();
            $table->string('service_typology')->nullable();
            $table->string('target')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discounts');
    }
};
