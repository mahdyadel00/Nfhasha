<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\SuccessResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\HyperPayService;

class HyperPayController extends Controller
{
    private $hyperPayService;

    public function __construct(HyperPayService $hyperPayService)
    {
        $this->hyperPayService = $hyperPayService;
    }
    public function initiatePayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $user = auth()->user();

        $request->validate([
            'paymentMethod' => 'required|in:visa,mastercard,mada,applepay,wallet,cash',
        ]);

        // الدفع عبر المحفظة
        if ($request->paymentMethod === 'wallet') {
            if ($user->balance < $order->total_cost) {
                return response()->json(['error' => 'Insufficient wallet balance'], 400);
            }

            // خصم المبلغ من المحفظة
            $user->balance -= $order->total_cost;
            $user->save();

            // تحديث حالة الطلب
            $order->status = 'completed';
            $order->payment_method = 'wallet';
            $order->save();

            // حذف جميع العروض المرتبطة بالطلب
            $order->offers()->delete();

            return response()->json(['message' => 'Payment successful via wallet']);
        }

        // الدفع عبر HyperPay
        $order->payment_method = ucfirst($request->paymentMethod);
        $order->status = 'completed';
        $order->save();

        $customerData = [
            'email' => auth()->user()->email,
            'street' => 'Na',
            'city' => 'NA',
            'state' => 'NA',
            'country' => 'NA',
            'postcode' => 'NA',
            'first_name' => auth()->user()->name,
            'last_name' => auth()->user()->name,
        ];

        $paymentData = $this->hyperPayService->initiatePayment(
            $order->total_cost,
            $request->paymentMethod,
            $customerData
        );

        if (!isset($paymentData['id'])) {
            return response()->json(['error' => 'Failed to initiate payment'], 500);
        }

        $order->payment_transaction_id = $paymentData['id'];
        $order->save();

        return response()->json([
            'message' => 'Redirect to payment page',
            'data' => $order->payment_transaction_id
        ]);
    }
    public function getPaymentStatus(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return ErrorResource::notFound('Order not found');
        }

        $order->update([
            'status' => $request->status,
            'payment_method' => $request->payment_method,
        ]);

        if ($request->status === 'completed') {
            $order->status = 'completed';
        } elseif ($request->status === 'failed') {
            $order->status = 'failed';
        } else {
            return ErrorResource::badRequest('Invalid status');
        }
        $order->save();

        return response()->json([
            'message' => 'Payment status updated successfully',
            'order_status' => $order->status,
        ]);
    }



    public function refundPayment(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)->where('user_id', auth()->id())->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        if (!$order->payment_transaction_id) {
            return response()->json(['error' => 'Transaction ID not found.'], 400);
        }

        if ($order->status !== 'completed') {
            return response()->json(['error' => 'Only completed orders can be refunded.'], 400);
        }

        $refundResponse = $this->hyperPayService->refundPayment($order->payment_transaction_id, $order->total_cost);

        if (!isset($refundResponse['result']['code']) || !str_contains($refundResponse['result']['code'], '000.100.')) {
            return response()->json(['error' => 'Refund failed', 'data' => $refundResponse], 400);
        }

        $order->status = 'refunded';
        $order->save();

        return response()->json(['message' => 'Refund successful', 'data' => $refundResponse]);
    }
    public function applePayCallback(Request $request)
    {
        return response()->json(['message' => 'Apple Pay callback received', 'data' => $request->all()]);
    }

    public function getCheckoutId($checkoutId)
    {
        $order = Order::where('payment_transaction_id', $checkoutId)->first();
        if ($order && $order->created_at->diffInMinutes(now()) > 30) {
            return response()->json(['error' => __('messages.checkout_id_expired')], 400);
        }


        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $response = $this->hyperPayService->getPaymentStatus($checkoutId, $order->payment_method);

        if (!$response instanceof \Illuminate\Http\Client\Response) {
            \Log::error('Unexpected response type from HyperPay API', ['response' => $response]);
            return response()->json(['error' => 'Unexpected response type'], 500);
        }


        // ✅ التحقق مما إذا كان الطلب فشل
        if ($response->failed()) {
            \Log::error('Failed to retrieve payment status from HyperPay', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => 'Failed to retrieve payment status',
                'details' => $response->body()
            ], 500);
        }

        // ✅ استخراج البيانات من الاستجابة
        $responseData = $response->json();
        $resultCode = $responseData['result']['code'] ?? null;

        if (!$resultCode) {
            \Log::error('Invalid response from HyperPay', ['response' => $responseData]);
            return response()->json(['error' => 'Invalid response from HyperPay'], 500);
        }

        // ✅ تحديث حالة الطلب بناءً على كود النتيجة
        $status = match ($resultCode) {
            '000.100.110' => 'paid',     // تمت عملية الدفع بنجاح
            '000.200.000' => 'pending',  // الدفع قيد الانتظار
            default => 'failed',         // فشل الدفع
        };

        $order->update(['status' => $status]);

        return response()->json([
            'message' => __('messages.payment_status_retrieved_successfully'),
            'order_status' => $order->status,
            'hyperpay_result_code' => $resultCode
        ]);
    }
}
