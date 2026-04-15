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
        Schema::table('rule', function (Blueprint $table) {
            $table->boolean('cap_status')->after('is_active')->default(false);
            $table->integer('cap')->after('is_active')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rule', function (Blueprint $table) {
            $table->dropColumn('cap_status');
            $table->dropColumn('cap');
        });
    }
};
