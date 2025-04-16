<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\SuccessResource;
use App\Models\Order;
use App\Models\WalletDeposit;
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
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'paymentMethod' => 'required|in:visa,mastercard,mada,applepay,wallet,cash', // إضافة wallet و cash زي initiatePayment
        ]);

        $user = auth()->user();
        $amount = $request->amount;
        $paymentMethod = $request->paymentMethod; // هنسيبها زي ما هي بدون strtolower

        // التحقق من طريقة الدفع إذا كانت wallet
        if ($paymentMethod === 'wallet') {
            if ($user->balance < $amount) {
                return response()->json(['error' => 'Insufficient wallet balance'], 400);
            }

            $user->balance -= $amount;
            $user->save();

            $deposit = WalletDeposit::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'payment_method' => 'wallet',
                'status' => 'completed',
            ]);

            return response()->json(['message' => 'Deposit successful via wallet']);
        }

        // إنشاء سجل الإيداع
        $deposit = WalletDeposit::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'payment_method' => ucfirst($paymentMethod), // استخدام ucfirst زي initiatePayment
            'status' => 'pending',
        ]);

        // إعداد بيانات العميل
        $email = $user->email ?? 'test@example.com';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            \Log::warning('Invalid user email in deposit', ['user_id' => $user->id, 'email' => $email]);
            $email = 'test@example.com';
        }

        $customerData = [
            'email' => $email,
            'street' => 'NA',
            'city' => 'NA',
            'state' => 'NA',
            'country' => 'NA',
            'postcode' => 'NA',
            'first_name' => $user->name ?? 'Unknown',
            'last_name' => $user->name ?? 'Unknown',
        ];

        // إرسال الطلب لـ HyperPay
        $paymentData = $this->hyperPayService->initiatePayment($amount, $paymentMethod, $customerData);

        if (!isset($paymentData['id'])) {
            $deposit->update(['status' => 'failed']);
            $errorMessage = $paymentData['error'] ?? 'Failed to initiate payment';
            $errorDetails = $paymentData['details'] ?? [];

            \Log::error('Failed to initiate deposit payment', [
                'user_id' => $user->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'error' => $errorMessage,
                'details' => $errorDetails,
            ]);

            return response()->json(
                [
                    'error' => $errorMessage,
                    'details' => $errorDetails,
                    'message' => 'Please contact support if this issue persists.',
                ],
                500,
            );
        }

        $deposit->update(['checkout_id' => $paymentData['id']]);

        // الـ response بنفس طريقة initiatePayment
        return response()->json([
            'message' => 'Redirect to payment page',
            'data' => $deposit->checkout_id,
        ]);
    }

    public function confirmDeposit(Request $request, $checkoutId)
    {
        $deposit = WalletDeposit::where('checkout_id', $checkoutId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$deposit) {
            return response()->json([
                'error' => __('messages.deposit_record_not_found'),
                'property_message' => __('messages.deposit_record_not_found_property')
            ], 404);
        }

        if ($deposit->created_at->diffInMinutes(now()) > 30) {
            $deposit->update(['status' => 'failed']);
            return response()->json([
                'error' => __('messages.checkout_id_expired'),
                'property_message' => __('messages.checkout_id_expired_property')
            ], 400);
        }

        $response = $this->hyperPayService->getPaymentStatus($checkoutId, $deposit->payment_method);

        if (!$response instanceof \Illuminate\Http\Client\Response) {
            \Log::error('نوع الرد غير متوقع من HyperPay API', ['response' => $response]);
            return response()->json([
                'error' => __('messages.unexpected_response_type'),
                'property_message' => __('messages.unexpected_response_type_property')
            ], 500);
        }

        if ($response->failed()) {
            \Log::error(__('messages.failed_to_retrieve_payment_status') . ': ' . $response->body());
            return response()->json([
                'error' => __('messages.failed_to_retrieve_payment_status'),
                'property_message' => __('messages.failed_to_retrieve_payment_status_property'),
                'details' => $response->body()
            ], 500);
        }

        $responseData = $response->json();
        $resultCode = $responseData['result']['code'] ?? null;

        if (!$resultCode) {
            \Log::error(__('messages.invalid_hyperpay_response') . ': ' . $response->body());
            return response()->json([
                'error' => __('messages.invalid_hyperpay_response'),
                'property_message' => __('messages.invalid_hyperpay_response_property')
            ], 500);
        }

        $status = match ($resultCode) {
            '000.100.110' => 'completed',
            '000.200.000' => 'pending',
            default => 'failed',
        };

        $deposit->update(['status' => $status]);

        if ($status === 'completed') {
            $user = $deposit->user;
            $user->balance += $deposit->amount;
            $user->save();

            return response()->json([
                'message' => __('messages.deposit_confirmed_successfully'),
                'property_message' => __('messages.deposit_confirmed_successfully_property'),
                'deposit_status' => $deposit->status,
                'hyperpay_result_code' => $resultCode,
            ]);
        } elseif ($status === 'pending') {
            return response()->json([
                'message' => __('messages.deposit_processing'),
                'property_message' => __('messages.deposit_processing_property'),
                'deposit_status' => $deposit->status,
                'hyperpay_result_code' => $resultCode,
            ]);
        } else {
            return response()->json([
                'error' => __('messages.deposit_failed'),
                'property_message' => __('messages.deposit_failed_property'),
                'deposit_status' => $deposit->status,
                'hyperpay_result_code' => $resultCode,
            ], 400);
        }
    }

    // باقي الدوال زي ما هي
    public function updateDepositStatus(Request $request, $checkoutId)
    {
        $deposit = WalletDeposit::where('checkout_id', $checkoutId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$deposit) {
            return response()->json(['error' => __('messages.deposit_record_not_found'),
                'property_message' => __('messages.deposit_record_not_found_property')
            ], 404);
        }

        if ($deposit->created_at->diffInMinutes(now()) > 30) {
            $deposit->update(['status' => 'failed']);
            return response()->json(['error' => __('messages.checkout_id_expired'),
                'property_message' => __('messages.checkout_id_expired_property')
            ], 400);
        }

        $response = $this->hyperPayService->getPaymentStatus($checkoutId, $deposit->payment_method);

        if (!$response instanceof \Illuminate\Http\Client\Response) {
            \Log::error(__('messages.unexpected_response_type') . ': ' . $response->body());
            return response()->json(['error' => __('messages.unexpected_response_type'),
                'property_message' => __('messages.unexpected_response_type_property')
            ], 500);
        }

        if ($response->failed()) {
            \Log::error(__('messages.failed_to_retrieve_payment_status') . ': ' . $response->body());
            return response()->json(
                [
                    'error' => __('messages.failed_to_retrieve_payment_status'),
                    'property_message' => __('messages.failed_to_retrieve_payment_status_property'),
                    'details' => $response->body(),
                ],
                500,
            );
        }

        $responseData = $response->json();
        $resultCode = $responseData['result']['code'] ?? null;

        if (!$resultCode) {
            \Log::error(__('messages.invalid_hyperpay_response') . ': ' . $response->body());
            return response()->json(['error' => __('messages.invalid_hyperpay_response'),
                'property_message' => __('messages.invalid_hyperpay_response_property')
            ], 500);
        }

        $status = match ($resultCode) {
            '000.100.110' => 'completed',
            '000.200.000' => 'pending',
            default => 'failed',
        };

        $deposit->update(['status' => $status]);

        if ($status === 'completed') {
            $user = $deposit->user;
            $user->balance += $deposit->amount;
            $user->save();
        }

        return response()->json([
            'message' => __('messages.deposit_status_updated_successfully'),
            'property_message' => __('messages.deposit_status_updated_successfully_property'),
            'deposit_status' => $deposit->status,
            'hyperpay_result_code' => $resultCode,
        ]);
    }

    public function initiatePayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $user = auth()->user();

        $request->validate([
            'paymentMethod' => 'required|in:visa,mastercard,mada,applepay,wallet,cash',
        ]);

        if ($request->paymentMethod === 'wallet') {
            if ($user->balance < $order->total_cost) {
                return response()->json(['error' => __('messages.insufficient_wallet_balance'),
                    'property_message' => __('messages.insufficient_wallet_balance_property')
                ], 400);
            }

            $user->balance -= $order->total_cost;
            $user->save();

            $order->status = 'completed';
            $order->payment_method = 'wallet';
            $order->save();

            $order->offers()->delete();

            return response()->json(['message' => __('messages.payment_successful_via_wallet'),
                'property_message' => __('messages.payment_successful_via_wallet_property')
            ]);
        }

        $order->payment_method = ucfirst($request->paymentMethod);
        $order->status = 'completed';
        $order->save();

        $email = $user->email ?? 'test@example.com';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = 'test@example.com';
        }

        $customerData = [
            'email' => $email,
            'street' => 'NA',
            'city' => 'NA',
            'state' => 'NA',
            'country' => 'NA',
            'postcode' => 'NA',
            'first_name' => $user->name ?? 'Unknown',
            'last_name' => $user->name ?? 'Unknown',
        ];

        $paymentData = $this->hyperPayService->initiatePayment($order->total_cost, $request->paymentMethod, $customerData);

        if (!isset($paymentData['id'])) {
            return response()->json(['error' => __('messages.failed_to_initiate_payment'),
                'property_message' => __('messages.failed_to_initiate_payment_property')
            ], 500);
        }

        $order->payment_transaction_id = $paymentData['id'];
        $order->save();

        return response()->json([
            'message' => __('messages.redirect_to_payment_page'),
            'data' => $order->payment_transaction_id,
        ]);
    }

    public function getPaymentStatus(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return new ErrorResource(__('messages.order_not_found'), 404);
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
            return new ErrorResource(__('messages.invalid_status'), 400);
        }
        $order->save();

        return response()->json([
            'message' => __('messages.payment_status_updated_successfully'),
            'property_message' => __('messages.payment_status_updated_successfully_property'),
            'order_status' => $order->status,
        ]);
    }

    public function refundPayment(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json(['error' => __('messages.order_not_found'),
                'property_message' => __('messages.order_not_found_property')
            ], 404);
        }

        if (!$order->payment_transaction_id) {
            return response()->json(['error' => __('messages.transaction_id_not_found'),
                'property_message' => __('messages.transaction_id_not_found_property')
            ], 400);
        }

        if ($order->status !== 'completed') {
            return response()->json(['error' => __('messages.only_completed_orders_can_be_refunded'),
                'property_message' => __('messages.only_completed_orders_can_be_refunded_property')
            ], 400);
        }

        $refundResponse = $this->hyperPayService->refundPayment($order->payment_transaction_id, $order->total_cost);

        if (!isset($refundResponse['result']['code']) || !str_contains($refundResponse['result']['code'], '000.100.')) {
            return response()->json(['error' => __('messages.refund_failed'),
                'property_message' => __('messages.refund_failed_property')
            ], 400);
        }

        $order->status = 'refunded';
        $order->save();

        return response()->json(['message' => __('messages.refund_successful'),
            'property_message' => __('messages.refund_successful_property')
        ]);
    }

    public function applePayCallback(Request $request)
    {
        return response()->json(['message' => __('messages.apple_pay_callback_received'),
            'property_message' => __('messages.apple_pay_callback_received_property')
        ]);
    }

    public function getCheckoutId($checkoutId)
    {
        $order = Order::where('payment_transaction_id', $checkoutId)->first();
        if ($order && $order->created_at->diffInMinutes(now()) > 30) {
            return response()->json(['error' => __('messages.checkout_id_expired')], 400);
        }

        if (!$order) {
            return response()->json(['error' => __('messages.order_not_found'),
                'property_message' => __('messages.order_not_found_property')
            ], 404);
        }

        $response = $this->hyperPayService->getPaymentStatus($checkoutId, $order->payment_method);

        if (!$response instanceof \Illuminate\Http\Client\Response) {
            \Log::error(__('messages.unexpected_response_type') . ': ' . $response->body());
            return response()->json(['error' => __('messages.unexpected_response_type'),
                'property_message' => __('messages.unexpected_response_type_property')
            ], 500);
        }

        if ($response->failed()) {
            \Log::error(__('messages.failed_to_retrieve_payment_status') . ': ' . $response->body());

            return response()->json(
                [
                    'error' => __('messages.failed_to_retrieve_payment_status'),
                    'property_message' => __('messages.failed_to_retrieve_payment_status_property'),
                    'details' => $response->body(),
                ],
                500,
            );
        }

        $responseData = $response->json();
        $resultCode = $responseData['result']['code'] ?? null;

        if (!$resultCode) {
            \Log::error(__('messages.invalid_hyperpay_response') . ': ' . $response->body());   
            return response()->json(['error' => __('messages.invalid_hyperpay_response'),
                'property_message' => __('messages.invalid_hyperpay_response_property')
            ], 500);
        }

        $status = match ($resultCode) {
            '000.100.110' => 'paid',
            '000.200.000' => 'pending',
            default => 'failed',
        };

        $order->update(['status' => $status]);

        return response()->json([
            'message' => __('messages.payment_status_retrieved_successfully'),
            'order_status' => $order->status,
            'hyperpay_result_code' => $resultCode,
        ]);
    }
}
