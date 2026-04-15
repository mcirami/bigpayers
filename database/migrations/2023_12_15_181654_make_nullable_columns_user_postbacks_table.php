<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeNullableColumnsUserPostbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_postbacks', function (Blueprint $table) {
            $table->string('deduction_url')->nullable()->change();
	        $table->string('free_sign_up_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_postbacks', function (Blueprint $table) {
	        $table->string('deduction_url')->change();
	        $table->string('free_sign_up_url')->change();
        });
    }
}
