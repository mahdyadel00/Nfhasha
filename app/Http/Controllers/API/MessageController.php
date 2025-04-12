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
            'chat_id'       => 'required',
            'message'       => 'required',
            'type'          => 'required',
        ]);

        // العثور على الطلب حسب الـ id
        $order = Order::where('status', 'accepted')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // تحديد من هو المرسل بناءً على الـ user_id
        if ($order->user_id == auth()->id()) {
            $request->merge(['sender_id' => $order->user_id]);
        } else {
            $request->merge(['sender_id' => $order->provider_id]);
        }

        // إنشاء الرسالة
        $message = Message::create($request->all());

        // إعدادات Pusher
        try {
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
            );

            // إرسال البيانات إلى القناة المحددة
            $pusher->trigger('chat-channel', 'chat-event', [
                'order_id'      => $order->id,
                'sender_id'     => $message->sender_id,
                'chat_id'       => $message->chat_id,
                'type'          => $message->type,
                'message'       => $message->message,
                'reservation_id' => $order->provider_id,
                'created_at'    => $message->created_at,
            ]);
        } catch (\Exception $e) {
            // في حالة حدوث خطأ أثناء الاتصال بـ Pusher
            return response()->json([
                'message' => 'Failed to send message via Pusher.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        // إرسال إشعار إلى الـ Firebase (إذا كان موجودًا)
        if (!empty($order->provider->fcm_token)) {
            try {
                $firebaseService = new FirebaseService();
                $firebaseService->sendNotificationToUser(
                    $order->provider->fcm_token,
                    'New message from ' . $order->user->name,
                    $message->message
                );
            } catch (\Exception $e) {
                // في حالة فشل إرسال الإشعار
                return response()->json([
                    'message' => 'Failed to send Firebase notification.',
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
