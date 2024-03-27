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
        Schema::create('fiscal_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('business_type')->nullable();
            $table->string('name')->nullable();
            $table->string('vat_number', 16)->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            $table->string('fiscal_code', 16)->nullable();
            $table->string('invoice_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('pec')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'fiscal_file_user')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fiscal_files');
    }
};
