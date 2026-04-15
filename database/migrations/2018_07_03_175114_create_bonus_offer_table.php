<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusOfferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_offers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('required_sales');
            $table->boolean('active')->default(1);
            $table->unsignedInteger('offer_id');
            $table->foreign('offer_id')->references('idoffer')->on('offer');
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
        Schema::dropIfExists('bonus_offers');
    }
}
