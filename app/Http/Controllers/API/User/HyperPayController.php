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

        // تنفيذ عملية الدفع
        $paymentData = $this->hyperPayService->initiatePayment(
            $order->total_cost,
            $request->paymentMethod,
            $customerData
        );


        // تحديث الطلب بمعرف الدفع
        if (isset($paymentData['id'])) {
            $order->payment_transaction_id = $paymentData['id'];
            $order->save();
        }

        $checkoutId = $paymentData['id']; // معرف الـ Checkout الذي تم إرجاعه من HyperPay
        $paymentUrl = "https://eu-test.oppwa.com/paymentWidgets.js?checkoutId={$checkoutId}";

        // حفظ معرف الـ Checkout في الطلب لتتبعه لاحقًا
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

}
