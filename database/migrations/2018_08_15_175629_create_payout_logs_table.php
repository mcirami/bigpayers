<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayoutLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payout_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('idrep')->on('rep');
            $table->double('revenue');
            $table->double('deductions');
            $table->double('bonuses');
            $table->double('referrals');
            $table->timestamp('start_of_week')->nullable();
            $table->timestamp('end_of_week')->nullable();

            $table->unique(['user_id', 'start_of_week', 'end_of_week'], 'prevent_dupes');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payout_logs');
    }
}
