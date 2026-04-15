<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_clients', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('client_id')->nullable();
            $table->string('client_secret', 100)->nullable();

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('idrep')->on('rep');


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
        Schema::dropIfExists('sms_clients');
    }
}
