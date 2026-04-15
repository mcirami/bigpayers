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
        Schema::table('permissions', function (Blueprint $table) {
	        $table->boolean('view_adv_reports')->default(false)->after('edit_report_permissions');
	        $table->boolean('view_all_users')->default(false)->after('create_affiliates');
	        $table->boolean('view_payouts')->default(false)->after('edit_report_permissions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
	        $table->dropColumn('view_payouts');
	        $table->dropColumn('view_all_users');
	        $table->dropColumn('view_adv_reports');
        });
    }
};
