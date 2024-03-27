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
            $table->string('ipratico_vat_id')->nullable()->after('ipratico_id');
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
            $table->dropColumn('ipratico_vat_id');
        });
    }
};
