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
        Schema::table('offer_caps', function (Blueprint $table) {
	        $table->boolean('time_block_status')->default(0);
	        $table->string('block_start_time')->nullable();
			$table->string('block_end_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_caps', function (Blueprint $table) {
            $table->dropColumn('time_block_status');
			$table->dropColumn('block_start_time');
			$table->dropColumn('block_end_time');
        });
    }
};
