<?php

namespace App\Http\Controllers\Sms;

use App\Http\Controllers\Controller;
use App\SMSClient;
use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\System\Session;

class SmsClientController extends Controller
{
    /**
     * Create's a third party SMS User account (called Worker) to assign to an affiliate
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function createSMSWorker(Request $request)
    {
        $all = $request->all();

        $all['phoneNumber'] = preg_replace('/[^0-9]/', '', $all['phoneNumber']);

        // have to replace all of them for validate to actually work ..
        $request->replace([
            'user_id' => $all['user_id'],
            'phoneNumber' => $all['phoneNumber'],
        ]);


        $this->validate($request, [
            'user_id' => 'required|min:1',
            'phoneNumber' => 'required|min:11|max:11',
        ]);


        $http = new Client();


        try {
            $response = $http->post(env('SMS_URL').'/worker/create', [
                    'form_params' => $request->all(),
                ]
            );
        } catch (ClientException $e) {
            return back()->withErrors($e->getMessage());
        }


        if ($response->getStatusCode() !== 200) {
            return back()->withErrors('uh oh');
        }

        $response = json_decode($response->getBody());


        $smsClient = new SMSClient();
        $smsClient->sms_user_id = $response->user_id;
        $smsClient->user_id = $request->user_id;

        $smsClient->save();


        return back();
    }

    /**
     * Gets the current user's SMSClient, assigned to a route and used by
     * SMS Vue components
     * @return array
     */
    public function getUsersClient()
    {
        $smsClient = Session::user()->smsClients()->first();
        if (is_null($smsClient)) {
            return ["User doesn't have a SMSClient"];
        }

        return $smsClient->toArray();
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::withRole(\App\Privilege::ROLE_AFFILIATE)
            ->whereNotIn('idrep', \DB::table('sms_clients')->pluck('user_id'))
            ->get();

        return view('sms.client-add', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $client = new SMSClient();


        $client->user_id = Session::userID();
        $client->client_id = $request->client_id;
        $client->client_secret = $request->client_secret;

        if ($client->save()) {
            return back()->with(['success' => 'Successfully saved client !']);
        }

        return back()->withErrors('Failed to save client!');
    }


    /**
     * Show the form for editing (the specified resource?).
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $smsClient = Session::user()->smsClients()->first();

        return view('sms.client-edit', compact('smsClient'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        $smsClient = SMSClient::find($request->get('id'));

        $smsClient->client_id = $request->client_id;
        $smsClient->client_secret = $request->client_secret;
        $smsClient->sms_user_id = $request->sms_user_id;

        if ($smsClient->save()) {
            return back();
        }

        return back()->withErrors('Failed to update sms client!');
    }

}
