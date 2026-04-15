<?php

namespace App\Http\Controllers;

use App\Company;
use App\Email;
use App\EmailPool;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RelevanceReactorController extends Controller
{

    public function distributeEmail(Request $request)
    {
        $companies = Company::all('subDomain')->toArray();
        $currentCompanies = Arr::flatten($companies);


        if (file_exists(\Storage::disk('local')->path('emailRotation.json'))) {
            $pickedCompanies = json_decode(\Storage::disk('local')->get('emailRotation.json'));
        } else {
            $pickedCompanies = [];
        }

        $unpickedCompanies = array_diff($currentCompanies, $pickedCompanies);


        if (empty($unpickedCompanies)) {
            $pickedCompanies = [];
            $temp = array_reverse($currentCompanies);
            $company = array_pop($temp);
        } else {
            $temp = array_reverse($unpickedCompanies);
            $company = array_pop($temp);
        }


        // change database
        \Config::set('database.connections.mysql.database', $company);


        $email = new Email();

        $email->email = $request->get('email');

        $email->save();

        $pickedCompanies[] = $company;


        \Storage::disk('local')->put('emailRotation.json', json_encode($pickedCompanies));

        return $company;
    }

    public function incomingEmail(Request $request)
    {
        $email = new Email();
        $email->email = $request->get('email');
//        $email->record_id = $request->get('recordId');
//        $email->lead_ip_address = inet_pton($request->get('lead_ipaddress'));

        $email->save();
    }
}
