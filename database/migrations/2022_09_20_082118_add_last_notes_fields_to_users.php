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
        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('last_notes_updated_at')->nullable()->after('last_notes');
            $table->unsignedBigInteger('last_notes_by_id')->nullable()->after('last_notes_updated_at');

            $table->foreign('last_notes_by_id', 'customer_stylist')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('customer_stylist');
            $table->dropColumn('last_notes_updated_at');
            $table->dropColumn('last_notes_by_id');
        });
    }
};
