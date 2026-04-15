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
        Schema::table('click_vars', function (Blueprint $table) {
            $table->string('encoded')->nullable()->after('sub5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('click_vars', function (Blueprint $table) {
            $table->dropColumn('encoded');
        });
    }
};
