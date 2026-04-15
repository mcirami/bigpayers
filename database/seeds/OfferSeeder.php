<?php

use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!\App\Offer::where('offer_name', 'Default CPC Offer')->exists()) {
            $cpc = new \App\Offer();
            $cpc->created_by = 1;
            $cpc->offer_name = 'Default CPC Offer';
            $cpc->url = '#clickid#';
            $cpc->is_public = \LeadMax\TrackYourStats\Offer\Offer::VISIBILITY_PRIVATE;
            $cpc->payout = 1.00;
            $cpc->status = 1;
            $cpc->campaign_id = \App\Campaign::all()->first()->id;
            $cpc->save();

            $cpc->affiliates()->sync(\App\User::withRole(3)->get());
        }


        factory(\App\Offer::class, 30)->create();
    }
}
