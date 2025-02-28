<?php

namespace App\Http\Controllers\Api;

use App\Models\Chat;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
    public function startChat(Request $request, $id)
    {
        $order = Order::where('status', 'accepted')->findOrFail($id);

        $otherUserId = ($order->user_id == auth()->id()) ? $order->provider_id : $order->user_id;

        $chat = Chat::firstOrCreate([
            'order_id'      => $order->id,
            'user_id'       => auth()->id(),
            'provider_id'   => $otherUserId,
        ]);

        return response()->json($chat);
    }


    public function chats($id)
    {
        $chats = Chat::where('order_id', $id)
            ->where(function ($query) {
                $query->where('user_id', auth()->id())
                    ->orWhere('provider_id', auth()->id());
            })
            ->get();

        return response()->json([
            'data' => $chats
        ]);
    }


    public function chat($order_id, $id)
    {
        $chat = Chat::where('order_id', $order_id)
            ->where(function ($query) {
                $query->where('user_id', auth()->id())
                    ->orWhere('provider_id', auth()->id());
            })
            ->where('id', $id)
            ->first();

        if (!$chat) {
            return response()->json(['message' => 'Chat not found or unauthorized'], 404);
        }

        return response()->json([
            'data'     => $chat,
            'messages' => $chat->messages()->orderBy('created_at', 'asc')->get() // order by created_at
        ]);
    }
}