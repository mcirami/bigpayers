<?php

use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!\App\Company::where('shortHand', 'trackyourstats')->exists()) {
            $company = new \App\Company();
            $company->shortHand = 'trackyourstats';
            $company->subDomain = 'trackyourstats';
            $company->companyName = 'LeadMax';
            $company->city = '';
            $company->state = '';
            $company->address = '';
            $company->zip = '';
            $company->telephone = '';
            $company->email = '';
            $company->skype = '';
            $company->login_url = '';
            $company->landing_page = '';
            $company->uid = 'test';
            $company->db_version = 1.38;
            $company->save();
        }
    }
}
