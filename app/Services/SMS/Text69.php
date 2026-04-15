<?php

namespace App\Services\SMS;


use GuzzleHttp\Client;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\System\Session;

class Text69 implements ShortMessageServiceInterface
{

    private $accessToken;

    public function __construct()
    {
        if (is_null(Session::user())) {
            return;
        }
        if (is_null(Session::user()->smsClients()->first())) {
            abort(400, "User doesn't have a SMS Client");
        }
        $response = (new Client())->get(env('SMS_URL') . '/user/' . Session::user()->smsClients()->first()->sms_user_id . "/token");

        $this->accessToken = json_decode($response->getBody())->accessToken;
    }

    public function getBearerToken()
    {
        return $this->accessToken;
    }


    public function patchConversation(Request $request)
    {

        $response = (new Client())->patch(env('SMS_URL') . '/api/conversations', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getBearerToken(),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => $request->all(),
        ]);

        return ((string)$response->getBody());
    }

    public function sendMessage(Request $request)
    {
        $multipartConverted = [];
        foreach ($request->all() as $name => $val) {
            $multipartConverted[] = ['name' => $name, 'contents' => $val];
        }

        if ($request->hasFile('image')) {
            $multipartConverted[] = [
                'name' => 'image',
                'filename' => $request->file('image')->getClientOriginalName(),
                'Mime-Type' => $request->file('image')->getMimeType(),
                'contents' => fopen($request->file('image')->getPathname(), 'r'),
            ];
        }

        $params = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getBearerToken(),
            ],
            'multipart' => $multipartConverted,
        ];


        $response = (new Client())->post(env('SMS_URL') . '/api/messages/send', $params);

        return ((string)$response->getBody());
    }

    public function getConversations()
    {

        $response = (new Client())->get(env('SMS_URL') . '/api/user/conversations/', [
            'headers' => ['Authorization' => 'Bearer ' . $this->getBearerToken()],
        ]);


        return ((string)$response->getBody());
    }

    public function getConversation($id)
    {

        $response = (new Client())->get(env('SMS_URL') . '/api/conversations/' . $id, [
            'headers' => ['Authorization' => 'Bearer ' . $this->getBearerToken()],
        ]);


        return ((string)$response->getBody());
    }

    public function getMessages($conversationId)
    {

        $response = (new Client)->get(env('SMS_URL') . '/api/conversations/' . $conversationId . '/messages', [
            'headers' => ['Authorization' => 'Bearer ' . $this->getBearerToken()],
        ]);

        return (string)$response->getBody();
    }

    public function markConversationAsRead($conversationId)
    {

        $response = (new Client)->patch(env('SMS_URL') . '/api/conversations/' . $conversationId . '/read-new-messages',
            [
                'headers' => ['Authorization' => 'Bearer ' . $this->getBearerToken()],
            ]);

        return (string)$response->getBody();
    }

}