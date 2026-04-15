<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        $this->call(CompanySeeder::class);
//        $this->call(DefaultUsersSeeder::class);
//        $this->call(OfferSeeder::class);
//        $this->call(AffiliateSeeder::class);
        $this->call(ClicksSeeder::class);
    }
}
