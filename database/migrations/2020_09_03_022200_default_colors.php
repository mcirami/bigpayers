<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DefaultColors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company', function (Blueprint $table) {
            $table->string('colors', 255)->default('000000;FFFFFF;323645;61527E;848896;A48BD5;F6F6F6;FFFFFF;404452;999999;E94038')->change();
        });
        DB::raw('UPDATE company set colors = "000000;FFFFFF;323645;61527E;848896;A48BD5;F6F6F6;FFFFFF;404452;999999;E94038"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company', function (Blueprint $table) {
            $table->string('colors', 255)->default('484848;FFFFFF;2A58AD;1D4C9E;82A7EB;FCED16;EAEEF1;FFFFFF;404452;999999;E94038')->change();
        });
        DB::raw('UPDATE company set colors = "484848;FFFFFF;2A58AD;1D4C9E;82A7EB;FCED16;EAEEF1;FFFFFF;404452;999999;E94038"');
    }
}
