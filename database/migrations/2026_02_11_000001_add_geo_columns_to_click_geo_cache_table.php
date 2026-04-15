<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('click_geo_cache', function (Blueprint $table) {
            $table->string('subDivision')->nullable()->after('country_code');
            $table->string('city')->nullable()->after('subDivision');
            $table->string('postal')->nullable()->after('city');
            $table->decimal('latitude', 10, 7)->nullable()->after('postal');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('click_geo_cache', function (Blueprint $table) {
            $table->dropColumn([
                'subDivision',
                'city',
                'postal',
                'latitude',
                'longitude',
            ]);
        });
    }
};
