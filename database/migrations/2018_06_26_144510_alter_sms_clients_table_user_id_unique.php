<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSmsClientsTableUserIdUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_clients', function (Blueprint $table) {
            $table->unique('user_id');
            $table->unsignedInteger('sms_user_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('sms_clients', 'sms_user_id')) {
            Schema::table('sms_clients', function (Blueprint $table) {
                $table->dropColumn('sms_user_id');
            });
        }

        if (collect(DB::select('SHOW INDEXES FROM sms_clients'))->pluck('Key_name')->contains('user_id')) {
            Schema::table('sms_clients', function (Blueprint $table) {
                $table->dropIndex('user_id');
            });
        }

    }
}
