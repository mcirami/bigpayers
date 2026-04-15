<?php

namespace App\Http\Controllers\Sms;

use App\Http\Controllers\Controller;
use App\Services\SMS\ShortMessageServiceInterface;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\System\Session;

class SmsApiController extends Controller
{

    protected $api;

    public function __construct(ShortMessageServiceInterface $api)
    {
        $this->api = $api;
    }

    public function getConversations()
    {
        return $this->api->getConversations();
    }

    public function sendMessage(Request $request)
    {
        return $this->api->sendMessage($request);
    }

    public function getMessages($conversationId)
    {
        return $this->api->getMessages($conversationId);
    }

    public function readNewMessages($conversationId)
    {
        return $this->api->markConversationAsRead($conversationId);
    }

    public function getConversation($id)
    {
        return $this->api->getConversation($id);
    }

    public function patchConversation(Request $request)
    {
        return $this->api->patchConversation($request);
    }

}
