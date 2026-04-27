<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE rep_has_offer
            INNER JOIN offer ON offer.idoffer = rep_has_offer.offer_idoffer
            SET rep_has_offer.payout = NULL
            WHERE rep_has_offer.payout = offer.payout
        ');

        DB::statement('ALTER TABLE rep_has_offer MODIFY payout DOUBLE NULL DEFAULT NULL');
    }

    public function down(): void
    {
        DB::statement('
            UPDATE rep_has_offer
            INNER JOIN offer ON offer.idoffer = rep_has_offer.offer_idoffer
            SET rep_has_offer.payout = offer.payout
            WHERE rep_has_offer.payout IS NULL
        ');

        DB::statement('ALTER TABLE rep_has_offer MODIFY payout DOUBLE NOT NULL DEFAULT 0');
    }
};
