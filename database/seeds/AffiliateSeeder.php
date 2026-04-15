<?php

use Illuminate\Database\Seeder;

class AffiliateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\User::class, 'affiliate', 20)->create()->each(function (\App\User $u) {
            $p = new \App\Privilege();
            $p->is_rep = 1;
            $p->user()->associate($u)->save();
            $u->offers()->sync(\App\Offer::all());
        });
    }
}
