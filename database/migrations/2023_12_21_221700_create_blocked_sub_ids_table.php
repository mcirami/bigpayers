<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlockedSubIdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blocked_sub_ids', function (Blueprint $table) {
            $table->increments('id');
			$table->unsignedInteger('rep_idrep');
			$table->foreign('rep_idrep')->references('idrep')->on('rep');
	        $table->string('sub_id');
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
        Schema::dropIfExists('blocked_sub_ids');
    }
}
