<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offer', function (Blueprint $table) {
            $table->double('affiliate_payout')->nullable()->after('payout');
            $table->double('manager_payout')->nullable()->after('affiliate_payout');
            $table->double('admin_payout')->nullable()->after('manager_payout');
        });
    }

    public function down(): void
    {
        Schema::table('offer', function (Blueprint $table) {
            $table->dropColumn([
                'affiliate_payout',
                'manager_payout',
                'admin_payout',
            ]);
        });
    }
};
