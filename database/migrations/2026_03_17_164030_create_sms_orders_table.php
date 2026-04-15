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
        Schema::create('sms_orders', function (Blueprint $table) {
            $table->id();
	        // Your internal reference if needed
	        $table->string('client_reference')->nullable()->index();

	        // SMSPool values
	        $table->string('smspool_order_id')->unique();
	        $table->string('phone_number')->nullable();

	        // Request details
	        $table->string('service');
	        $table->string('country', 8);
	        $table->string('pool')->nullable();

	        // pending, received, cancelled, expired, refunded, failed
	        $table->string('status')->default('pending')->index();

	        // Received SMS data
	        $table->string('code')->nullable();
	        $table->text('full_sms')->nullable();
	        $table->timestamp('received_at')->nullable();

	        // Helpful for debugging
	        $table->json('raw_order_response')->nullable();
	        $table->json('raw_last_check_response')->nullable();
	        $table->json('raw_webhook_payload')->nullable();

	        // Optional timing
	        $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_orders');
    }
};
