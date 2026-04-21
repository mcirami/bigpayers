<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $indexNames = collect(DB::select('SHOW INDEX FROM rep'))
            ->pluck('Key_name')
            ->all();

        if (!in_array('rep_referrer_status_idx', $indexNames, true)) {
            DB::statement('ALTER TABLE rep ADD INDEX rep_referrer_status_idx (referrer_repid, status)');
        }
    }

    public function down(): void
    {
        $indexNames = collect(DB::select('SHOW INDEX FROM rep'))
            ->pluck('Key_name')
            ->all();

        if (in_array('rep_referrer_status_idx', $indexNames, true)) {
            DB::statement('ALTER TABLE rep DROP INDEX rep_referrer_status_idx');
        }
    }
};
