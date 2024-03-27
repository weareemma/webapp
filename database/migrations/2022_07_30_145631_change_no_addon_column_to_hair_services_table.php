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
            $table->renameColumn('no_addon', 'dry_style');
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
            $table->renameColumn('dry_style', 'no_addon');
        });
    }
};
