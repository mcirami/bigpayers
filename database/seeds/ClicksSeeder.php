<?php

namespace Database\Seeders;
use App\Click;
use App\Conversion;
use Database\Factories\ConversionFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClicksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void {
        foreach (\App\User::withRole(3)->get() as $affiliate) {
            $offerIds = $affiliate->offers->pluck('idoffer');
            for ($i = 0; $i < 100; $i++) {
                $click = Click::factory()->make();
                $click->offer_idoffer = $offerIds[rand(0, count($offerIds) - 1)];
                $click->rep_idrep = $affiliate->idrep;
                $click->save();

				DB::table('click_vars')->insert([
					'click_id'  => $click->idclicks,
					'url'       => '/?repid=' . $affiliate->idrep . '&offerid=' . $click->offer_idoffer . 'sub1=' . $affiliate->user_name,
					'sub1'      => $affiliate->user_name
				]);

                /*if (rand(0, 4) == 0) {
                    $freeSignUp = new \App\FreeSignUp();
                    $freeSignUp->user_id = $affiliate->idrep;
                    $freeSignUp->click_id = $click->idclicks;
                    $freeSignUp->timestamp = \Carbon\Carbon::now();
                    $freeSignUp->save();
                }*/

                /*if (rand(0, 4) == 1) {
                    $pConversion = factory(\App\PendingConversion::class)->make();
                    $pConversion->click_id = $click->idclicks;
                    $pConversion->save();
                }*/

                if (rand(0, 4) == 2) {
                    $conversion = Conversion::factory()->make();
                    $conversion->user_id = $affiliate->idrep;
                    $conversion->click_id = $click->idclicks;
                    $conversion->save();

                    /*if (rand(0, 2) == 0) {
                        $deduction = factory(\App\Deduction::class)->make();
                        $deduction->conversion_id = $conversion->id;
                        $deduction->save();
                    }*/
                }

            }
        }
    }
}
