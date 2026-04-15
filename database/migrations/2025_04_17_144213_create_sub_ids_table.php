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
        Schema::create('sub_ids', function (Blueprint $table) {
            $table->id();
			$table->unsignedInteger('idrep');
	        $table->foreign('idrep')
	              ->references('idrep')
	              ->on('rep')
	              ->onDelete('cascade');
	        $table->string('sub_id');
	        $table->index('idrep');                         // handy for look‑ups by user
	        $table->index('sub_id');                        // handy for global searches
	        $table->unique(['idrep', 'sub_id']);            // “sub_id” must be unique per user
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_ids');
    }
};
