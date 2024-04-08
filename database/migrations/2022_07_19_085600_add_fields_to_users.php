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
            $table->string('surname')->nullable()->after('name');
            $table->string('fiscal_code')->nullable()->after('password');
            $table->json('stores')->nullable()->after('fiscal_code');
            $table->string('tamigo_code')->nullable()->after('stores');
            $table->boolean('afro')->default(false)->nullable()->after('tamigo_code');
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
            $table->dropColumn('surname');
            $table->dropColumn('fiscal_code');
            $table->dropColumn('stores');
            $table->dropColumn('tamigo_code');
            $table->dropColumn('afro');
        });
    }
};
