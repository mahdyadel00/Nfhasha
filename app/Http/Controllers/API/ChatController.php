<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ErrorResource;
use App\Models\Chat;
use App\Models\Order;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function startChat(Request $request, $id)
    {
        $order = Order::where('status', 'accepted')->find($id);

        if(!$order) {
            return new ErrorResource([
                'message' => __('messages.order_not_found')
            ]);
        }

        if(!in_array(auth()->id(), [$order->user_id, $order->provider_id])) {
            return new ErrorResource([
                'message' => __('messages.unauthorized')
            ]);
        }

        if(!$order->provider_id) {
            return new ErrorResource([
                'message' => __('messages.provider_not_assigned')
            ]);
        }

        if(!$order->user_id) {
            return new ErrorResource([
                'message' => __('messages.user_not_assigned')
            ]);
        }

        if(!$order->status == 'accepted') {
            return new ErrorResource([
                'message' => __('messages.order_not_accepted')
            ]);
        }

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
            'details'     => $chat,
            'data'        => $chat->messages()->orderBy('created_at', 'asc')->get() // order by created_at
        ]);
    }
}