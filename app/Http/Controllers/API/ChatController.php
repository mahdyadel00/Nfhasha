<?php

namespace App\Http\Controllers\Api;

use App\Models\Chat;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
    public function startChat(Request $request , $id) {
        $order = Order::where('status' , 'accepted')->find($id);

        if($order->user_id == auth()->id()) {
            $chat = Chat::firstOrCreate([
                'order_id'      => $order->id,
                'user_id'       => auth()->id(),
                'provider_id'   => $order->provider_id,
            ]);
        } else {
            $chat = Chat::firstOrCreate([
                'order_id'      => $order->id,
                'user_id'       => auth()->id(),
                'provider_id'   => $order->provider_id,
            ]);
        }
        return response()->json($chat);
    }

    public function chats() {

        $chats = Chat::where('user_id' , auth()->id())->orWhere('provider_id' , auth()->id())->get();

        return response()->json([
            'data' => $chats
        ]);
    }


    public function chat($id) {
        $chat = Chat::find($id);

        if(!$chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        return response()->json([
            'data'      => $chat,
            'messages' => $chat->messages
        ]);
    }
}