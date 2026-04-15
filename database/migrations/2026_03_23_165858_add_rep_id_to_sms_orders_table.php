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
        Schema::table('sms_orders', function (Blueprint $table) {
	        $table->unsignedInteger('rep_id')->nullable()->after('id');

	        $table->index('rep_id', 'sms_orders_rep_id_index');
	        $table->index(['rep_id', 'created_at'], 'sms_orders_rep_id_created_at_index');
	        $table->index(['rep_id', 'status', 'created_at'], 'sms_orders_rep_status_created_at_index');

	        $table->foreign('rep_id', 'sms_orders_rep_id_foreign')
	              ->references('idrep')
	              ->on('rep')
	              ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_orders', function (Blueprint $table) {
	        $table->dropForeign('sms_orders_rep_id_foreign');
	        $table->dropIndex('sms_orders_rep_id_index');
	        $table->dropIndex('sms_orders_rep_id_created_at_index');
	        $table->dropIndex('sms_orders_rep_status_created_at_index');
	        $table->dropColumn('rep_id');
        });
    }
};
