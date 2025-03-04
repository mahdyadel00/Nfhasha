<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
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
        $request->validate([
            'paymentMethod' => 'required|in:visa,mada',
        ]);


        $order->payment_method = $request->paymentMethod;
        $order->status = 'completed';
        $order->save();

        $customerData = [
            'email'             => auth()->user()->email,
            'street'            => 'Na',
            'city'              => 'NA',
            'state'             => 'NA',
            'country'           => 'NA',
            'postcode'          => 'NA',
            'first_name'        => auth()->user()->name,
            'last_name'         => auth()->user()->name,
        ];

        $paymentData = $this->hyperPayService->initiatePayment(
            $order->total_cost,
            $request->paymentMethod,
            $customerData
        );

        if (isset($paymentData['id'])) {
            $order->payment_transaction_id = $paymentData['id'];
            $order->save();
        }

        $checkoutId = $paymentData['id'];
        $paymentUrl = "https://eu-prod.oppwa.com/v1/checkouts/{$checkoutId}/payment";

        $order->payment_transaction_id = $checkoutId;
        $order->status = 'pending';
        $order->save();

        return response()->json([
            'message' => 'Redirect to payment page',
            'url' => $paymentUrl
        ]);



    }

    public function getPaymentStatus(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        if (!$order->payment_transaction_id) {
            return response()->json([
                'error' => 'Checkout ID not found for this order.'
            ], 400);
        }

        $status = $this->hyperPayService->getPaymentStatus($order->payment_transaction_id);

        return response()->json($status);
    }

    public function refundPayment(Request $request, $orderId)
{
    $order = Order::findOrFail($orderId)->where('user_id', auth()->id())->first();

    if (!$order->payment_transaction_id) {
        return response()->json(['error' => 'Transaction ID not found.'], 400);
    }

    if ($order->status !== 'completed') {
        return response()->json(['error' => 'Only completed orders can be refunded.'], 400);
    }

    $refundResponse = $this->hyperPayService->refundPayment($order->payment_transaction_id, $order->total_cost);
    dd($refundResponse);

    if (isset($refundResponse['result']) && str_contains($refundResponse['result']['code'], '000.100.')) {
        $order->status = 'refunded';
        $order->save();

        return response()->json(['message' => 'Refund successful', 'data' => $refundResponse]);
    }

    return response()->json(['error' => 'Refund failed', 'data' => $refundResponse], 400);
}


}