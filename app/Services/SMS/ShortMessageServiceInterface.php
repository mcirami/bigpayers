<?php
namespace App\Services\SMS;


use Illuminate\Http\Request;

interface ShortMessageServiceInterface
{
    public function sendMessage(Request $request);

    public function getConversations();

    public function getConversation($id);

    public function getMessages($conversationId);

    public function markConversationAsRead($conversationId);

    public function patchConversation(Request $request);

}