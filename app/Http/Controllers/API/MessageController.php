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
        // التحقق من البيانات المدخلة
        $request->validate([
            'chat_id'   => 'required',
            'message'   => 'required',
            'type'      => 'required',
        ]);

        // العثور على الطلب حسب الـ id بشرط يكون مقبول
        $order = Order::where('status', 'accepted')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // تحديد المرسل والمستلم
        if ($order->user_id == auth()->id()) {
            $request->merge(['sender_id' => $order->user_id]);
            $receiverToken = $order->provider->fcm_token ?? null;
            $receiverName = $order->provider->name ?? 'Provider';
        } else {
            $request->merge(['sender_id' => $order->provider_id]);
            $receiverToken = $order->user->fcm_token ?? null;
            $receiverName = $order->user->name ?? 'User';
        }

        // إنشاء الرسالة
        $message = Message::create($request->all());

        // إرسال البيانات عبر Pusher
        try {
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
            );

            $pusher->trigger('chat-channel', 'chat-event', [
                'order_id'       => $order->id,
                'sender_id'      => $message->sender_id,
                'chat_id'        => $message->chat_id,
                'type'           => $message->type,
                'message'        => $message->message,
                'reservation_id' => $order->provider_id,
                'created_at'     => $message->created_at,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send message via Pusher.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        // إرسال إشعار Firebase للطرف الآخر فقط
        if (!empty($receiverToken)) {
            try {
                $firebaseService = new FirebaseService();

                $title = __('messages.new_message'); // العنوان مترجم
                $body = $message->message; // الرسالة نفسها في الـ body

                $extraData = [
                    'order_id' => $order->id,
                    'type'     => 'message',
                ];

                $firebaseService->sendNotificationToUser(
                    $receiverToken,
                    $title,
                    $body,
                    $extraData
                );
            } catch (\Exception $e) {
                return response()->json([
                    'message' => __('Failed to send notification.'),
                    'error'   => $e->getMessage(),
                ], 500);
            }
        }

        // إرسال رد بنجاح
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
