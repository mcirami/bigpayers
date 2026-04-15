<?php

use App\Privilege;
use Illuminate\Database\Seeder;

class DefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!\App\User::where('user_name', 'admin')->exists()) {
            $admin = new \App\User();
            $admin->user_name = 'admin';
            $admin->referrer_repid = 1;
            $admin->email = 'admin@' . parse_url(env('APP_URL'))['host'];
            $admin->password = Hash::make('admin123');
            $admin->status = 1;
            $admin->rep_timestamp = \Carbon\Carbon::now();
            $admin->save();
            $p = new Privilege();
            $p->is_admin = 1;
            $p->user()->associate($admin)->save();
        }

        if (!\App\User::where('user_name', 'manager')->exists()) {
            $manager = new \App\User();
            $manager->user_name = 'manager';
            $manager->referrer_repid = $admin->idrep;
            $manager->email = 'manager@' . parse_url(env('APP_URL'))['host'];
            $manager->password = Hash::make('manager123');
            $manager->status = 1;
            $manager->rep_timestamp = \Carbon\Carbon::now();
            $manager->save();
            $p = new Privilege();
            $p->is_manager = 1;
            $p->user()->associate($manager)->save();
        }

        if (!\App\User::where('user_name', 'affiliate')->exists()) {
            $affiliate = new \App\User();
            $affiliate->user_name = 'affiliate';
            $affiliate->referrer_repid = $manager->idrep;
            $affiliate->email = 'affiliate@' . parse_url(env('APP_URL'))['host'];
            $affiliate->password = Hash::make('manager123');
            $affiliate->status = 1;
            $affiliate->rep_timestamp = \Carbon\Carbon::now();
            $affiliate->save();
            $p = new Privilege();
            $p->is_rep = 1;
            $p->user()->associate($affiliate)->save();
        }
    }
}
