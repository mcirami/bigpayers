<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAggregateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aggregate_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('unique_clicks')->default(0);
            $table->unsignedBigInteger('free_sign_ups')->default(0);
            $table->unsignedBigInteger('pending_conversions')->default(0);
            $table->unsignedBigInteger('conversions')->default(0);
            $table->unsignedBigInteger('revenue')->default(0);
            $table->unsignedBigInteger('deductions')->default(0);
            $table->date('aggregate_date');
            $table->timestamps();

            // these naming conventions >.>
            $table->foreign('user_id')->references('idrep')->on('rep');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('aggregate_reports', function (Blueprint $table) {
            $table->dropForeign('user_id');
        });
        Schema::dropIfExists('aggregate_reports');
    }
}
