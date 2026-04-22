<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company', function (Blueprint $table) {
            $table->string('messenger_type')->nullable()->after('skype');
            $table->string('messenger_username')->nullable()->after('messenger_type');
            $table->boolean('allow_register')->default(true)->after('login_theme');
        });
    }

    public function down(): void
    {
        Schema::table('company', function (Blueprint $table) {
            $table->dropColumn(['messenger_type', 'messenger_username', 'allow_register']);
        });
    }
};
