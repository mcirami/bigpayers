<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailPoolsPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->tinyInteger(\LeadMax\TrackYourStats\User\Permissions::EMAIL_POOLS)->default(0);
        });

        DB::statement('UPDATE permissions SET email_pools = 1 WHERE aff_id = 1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(\LeadMax\TrackYourStats\User\Permissions::EMAIL_POOLS);
        });
    }
}
