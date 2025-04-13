<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Order;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Pusher\Pusher;

class MessageController extends Controller
{
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'chat_id' => 'required',
            'message' => 'required',
            'type' => 'required',
        ]);

        $order = Order::where('status', 'accepted')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // تحديد المرسل والمستلم
        if ($order->provider_id == auth()->id()) {
            // الـ provider هو المرسل
            $request->merge(['sender_id' => $order->provider_id]);
            $receiverToken = $order->user->fcm_token ?? null; // إرسال الإشعار للـ client
            $receiverName = $order->user->name ?? 'User';
        } else {
            // الـ user هو المرسل
            $request->merge(['sender_id' => $order->user_id]);
            $receiverToken = $order->provider->fcm_token ?? null; // إرسال الإشعار للـ provider
            $receiverName = $order->provider->name ?? 'Provider';
        }

        // إنشاء الرسالة
        $message = Message::create($request->all());

        // إرسال البيانات عبر Pusher
        try {
            $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]);

            $pusher->trigger('chat-channel', 'chat-event', [
                'order_id' => $order->id,
                'sender_id' => $message->sender_id,
                'chat_id' => $message->chat_id,
                'type' => $message->type,
                'message' => $message->message,
                'reservation_id' => $order->provider_id,
                'order_status' => $order->status, // إضافة حالة الطلب
                'created_at' => $message->created_at,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'Failed to send message via Pusher.',
                    'error' => $e->getMessage(),
                ],
                500,
            );
        }

        // إرسال إشعار Firebase للطرف الآخر فقط
        if (!empty($receiverToken)) {
            try {
                $firebaseService = new FirebaseService();

                $title = __('messages.new_message');
                $body = $message->message;

                $extraData = [
                    'order_id' => $order->id,
                    'type' => 'message',
                    'order_status' => $order->status, // إضافة حالة الطلب
                ];

                $firebaseService->sendNotificationToUser($receiverToken, $title, $body, $extraData);
            } catch (\Exception $e) {
                return response()->json(
                    [
                        'message' => __('Failed to send notification.'),
                        'error' => $e->getMessage(),
                    ],
                    500,
                );
            }
        }

        return response()->json(['message' => __('messages.message_sent')]);
    }

    public function messages($id)
    {
        $chat = Chat::find($id);

        if (!$chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        $messages = $chat->messages;

        return response()->json(['data' => $messages]);
    }
}
