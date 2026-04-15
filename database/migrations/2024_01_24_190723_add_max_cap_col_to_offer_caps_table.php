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
	        $table->boolean('max_cap_status')->default(0);
            $table->integer('max_cap')->nullable();
	        $table->integer('is_max_capped')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_caps', function (Blueprint $table) {
	        $table->dropColumn('max_cap');
			$table->dropColumn('max_cap_status');
			$table->dropColumn('is_max_capped');
        });
    }
};
