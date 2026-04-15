<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('country_list', function (Blueprint $table) {
            $table->integer('cap')->after('country_code')->default(0);
            $table->boolean('cap_status')->after('country_code')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('country_list', function (Blueprint $table) {
            $table->dropColumn('cap');
            $table->dropColumn('cap_status');
        });
    }
};
