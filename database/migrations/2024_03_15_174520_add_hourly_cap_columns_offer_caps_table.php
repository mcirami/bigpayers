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
	        $table->boolean('hourly_cap_status')->after('is_capped')->default(0);
	        $table->integer('hourly_cap')->after('hourly_cap_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_caps', function (Blueprint $table) {
            $table->dropColumn('hourly_cap_status');
			$table->dropColumn('hourly_cap');
        });
    }
};
