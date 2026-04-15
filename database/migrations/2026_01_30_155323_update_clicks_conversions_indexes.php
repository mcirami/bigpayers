<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
	    // --- CLICKS: ip_address prefix index (45 chars covers IPv6)
	    if ($this->isMySql() && !$this->indexExists('clicks', 'clicks_ip_idx')) {
		    DB::statement("ALTER TABLE clicks ADD INDEX clicks_ip_idx (ip_address(45))");
	    }

	    // --- CLICKS: country_code index (prefix or full depending on your choice)
	    // If you want to keep country_code as VARCHAR(255) for now:
	    if ($this->isMySql() && !$this->indexExists('clicks', 'clicks_country_idx')) {
		    DB::statement("ALTER TABLE clicks ADD INDEX clicks_country_idx (country_code(2))");
	    }

	    if ($this->isMySql() && !$this->indexExists('clicks', 'clicks_report_time_offer_type_ip')) {
		    DB::statement("
                ALTER TABLE clicks
                ADD INDEX clicks_report_time_offer_type_ip (first_timestamp, offer_idoffer, click_type, ip_address(45))
            ");
	    }

	    // --- CONVERSIONS: composite (timestamp, click_id) (optional)
	    if ($this->isMySql() && !$this->indexExists('conversions', 'conv_time_click_idx')) {
		    DB::statement("ALTER TABLE conversions ADD INDEX conv_time_click_idx (timestamp, click_id)");
	    }

	    // --- CLICKS: remove redundant unique index on idclicks (optional)
	    // PRIMARY already enforces uniqueness.
	    if ($this->isMySql() && $this->indexExists('clicks', 'idclicks_UNIQUE')) {
		    DB::statement("ALTER TABLE clicks DROP INDEX idclicks_UNIQUE");
	    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
	    // Reverse what we added (safe drops)
	    if ($this->isMySql() && $this->indexExists('clicks', 'clicks_ip_idx')) {
		    DB::statement("ALTER TABLE clicks DROP INDEX clicks_ip_idx");
	    }

	    if ($this->isMySql() && $this->indexExists('clicks', 'clicks_country_idx')) {
		    DB::statement("ALTER TABLE clicks DROP INDEX clicks_country_idx");
	    }

	    if ($this->isMySql() && $this->indexExists('clicks', 'clicks_report_time_offer_type_ip')) {
		    DB::statement("ALTER TABLE clicks DROP INDEX clicks_report_time_offer_type_ip");
	    }

	    if ($this->isMySql() && $this->indexExists('conversions', 'conv_time_click_idx')) {
		    DB::statement("ALTER TABLE conversions DROP INDEX conv_time_click_idx");
	    }
    }

	private function isMySql(): bool
	{
		return DB::getDriverName() === 'mysql';
	}

	private function indexExists(string $table, string $indexName): bool
	{
		// Works on MySQL
		if (!$this->isMySql()) {
			return false;
		}

		$dbName = DB::getDatabaseName();

		$result = DB::selectOne("
            SELECT COUNT(1) AS cnt
            FROM information_schema.statistics
            WHERE table_schema = ?
              AND table_name = ?
              AND index_name = ?
        ", [$dbName, $table, $indexName]);

		return (int) ($result->cnt ?? 0) > 0;
	}
};
