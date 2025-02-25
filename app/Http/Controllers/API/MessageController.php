<?php

namespace App\Http\Controllers\Api;

use App\Models\Message;
use App\Events\NewMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MessageController extends Controller
{
    public function sendMessage(Request $request) {
        $message = Message::create([
            'chat_id' => $request->chat_id,
            'sender_id' => $request->sender_id,
            'message' => $request->message,
            'type' => $request->type,
        ]);
        broadcast(new NewMessage($message))->toOthers();
        return response()->json($message);
    }
}
