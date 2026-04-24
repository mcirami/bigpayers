<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predefined_offer_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 24);
            $table->string('name');
            $table->string('rule_name')->nullable();
            $table->unsignedInteger('redirect_offer')->default(0);
            $table->boolean('deny')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('cap_amount')->default(0);
            $table->boolean('cap_status')->default(false);
            $table->longText('items_json');
            $table->timestamps();

            $table->index(['type', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predefined_offer_rules');
    }
};
