<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
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
            'paymentMethod' => 'required|in:visa,mada,wallet',
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
        $order->payment_method = $request->paymentMethod;
        $order->status = 'pending';
        $order->save();

        $customerData = [
            'email'       => auth()->user()->email,
            'street'      => 'Na',
            'city'        => 'NA',
            'state'       => 'NA',
            'country'     => 'NA',
            'postcode'    => 'NA',
            'first_name'  => auth()->user()->name,
            'last_name'   => auth()->user()->name,
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
            'url' => "https://eu-test.oppwa.com/v1/paymentWidgets.js?checkoutId={$paymentData['id']}",
        ]);
    }
    public function getPaymentStatus($paymentTransactionId, $paymentMethod)
    {
        $order = Order::where('payment_transaction_id', $paymentTransactionId)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $response = $this->hyperPayService->getPaymentStatus($paymentTransactionId, $paymentMethod);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to get payment status', 'details' => $response->body()], 500);
        }

        $responseData = $response->json();

        // تحديث حالة الطلب بناءً على كود الاستجابة
        $resultCode = $responseData['result']['code'] ?? null;
        if ($resultCode === '000.100.110') {
            $order->update(['status' => 'paid']);
        } elseif ($resultCode === '000.200.000') {
            $order->update(['status' => 'pending']);
        } else {
            $order->update(['status' => 'failed']);
        }

        return response()->json([
            'status' => $order->status,
            'response' => $responseData
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
}
