<?php

namespace App\Http\Controllers\API\Provider;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\WalletDeposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\HyperPayService;

class WalletController extends Controller
{
    private $hyperPayService;

    public function __construct(HyperPayService $hyperPayService)
    {
        $this->hyperPayService = $hyperPayService;
    }

    public function index()
    {
        $user = Auth::user();
        return response()->json([
            'status' => true,
            'data' => [
                'balance' => $user->balance
            ]
        ]);
    }

    public function withdraw(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $user->balance,
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string'
        ]);

        if ($user->balance < $request->amount) {
            return response()->json([
                'status' => false,
                'message' => __('messages.insufficient_balance'),
                'property' => __('messages.insufficient_balance_property')
            ], 400);
        }

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => true,
            'message' => __('messages.withdrawal_request_sent'),
            'property' => __('messages.withdrawal_request_sent_property'),
            'data' => $withdrawal
        ]);
    }

    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'paymentMethod' => 'required|in:visa,mastercard,mada,applepay,wallet,cash',
        ]);

        $user = Auth::user();
        $amount = $request->amount;
        $paymentMethod = $request->paymentMethod;

        // Check if payment method is wallet
        if ($paymentMethod === 'wallet') {
            if ($user->balance < $amount) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.insufficient_balance'),
                    'property' => __('messages.insufficient_balance_property')
                ], 400);
            }

            $user->balance -= $amount;
            $user->save();

            $deposit = WalletDeposit::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'payment_method' => 'wallet',
                'status' => 'completed',
            ]);

            return response()->json([
                'status' => true,
                'message' => __('messages.deposit_successful'),
                'property' => __('messages.deposit_successful_property'),
                'data' => $deposit
            ]);
        }

        // Create deposit record for other payment methods
        $deposit = WalletDeposit::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'payment_method' => ucfirst($paymentMethod),
            'status' => 'pending',
        ]);

        // Prepare customer data
        $email = $user->email ?? 'test@example.com';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            \Log::warning(__('messages.invalid_user_email') . ': ' . $email);
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

        // Send request to HyperPay
        $paymentData = $this->hyperPayService->initiatePayment($amount, $paymentMethod, $customerData);

        if (!isset($paymentData['id'])) {
            $deposit->update(['status' => 'failed']);
            $errorMessage = $paymentData['error'] ?? 'Failed to initiate payment';
            $errorDetails = $paymentData['details'] ?? [];

            \Log::error(__('messages.failed_to_initiate_deposit_payment') . ': ' . $errorMessage);

            return response()->json([
                'status' => false,
                'message' => $errorMessage,
                'property' => $errorDetails,
            ], 500);
        }

        $deposit->update(['checkout_id' => $paymentData['id']]);

        return response()->json([
            'status' => true,
            'message' => __('messages.redirect_to_payment'),
            'data' => $deposit->checkout_id
        ]);
    }

    public function getWithdrawals()
    {
        $user = Auth::user();
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $withdrawals
        ]);
    }

    public function getDeposits()
    {
        $user = Auth::user();
        $deposits = WalletDeposit::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $deposits
        ]);
    }

    public function confirmDeposit(Request $request, $checkoutId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.login_first'),
                'property' => __('messages.login_first_property')
            ], 401);
        }

        $deposit = WalletDeposit::where('checkout_id', $checkoutId)
            ->where('user_id', $user->id)
            ->first();

        if (!$deposit) {
            return response()->json([
                'status' => false,
                'message' => __('messages.deposit_record_not_found'),
                'property' => __('messages.deposit_record_not_found_property')
            ], 404);
        }

        if ($deposit->created_at->diffInMinutes(now()) > 30) {
            $deposit->update(['status' => 'failed']);
            return response()->json([
                'status' => false,
                'message' => __('messages.checkout_id_expired'),
                'property' => __('messages.checkout_id_expired_property')
            ], 400);
        }

        $response = $this->hyperPayService->getPaymentStatus($checkoutId, $deposit->payment_method);

        if (!$response instanceof \Illuminate\Http\Client\Response) {
            \Log::error('Unexpected response type from HyperPay API', ['response' => $response]);
            return response()->json([
                'status' => false,
                'message' => __('messages.unexpected_response_type'),
                'property' => __('messages.unexpected_response_type_property')
            ], 500);
        }

        if ($response->failed()) {
            \Log::error('Failed to retrieve payment status from HyperPay', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return response()->json([
                'status' => false,
                'message' => __('messages.failed_to_retrieve_payment_status'),
                'property' => __('messages.failed_to_retrieve_payment_status_property'),
                'details' => $response->body()
            ], 500);
        }

        $responseData = $response->json();
        $resultCode = $responseData['result']['code'] ?? null;

        if (!$resultCode) {
            \Log::error('Invalid response from HyperPay', ['response' => $responseData]);
            return response()->json([
                'status' => false,
                'message' => __('messages.invalid_hyperpay_response'),
                'property' => __('messages.invalid_hyperpay_response_property')
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
                'status' => true,
                'message' => __('messages.deposit_confirmed_successfully'),
                'property' => __('messages.deposit_confirmed_successfully_property'),
                'deposit_status' => $deposit->status,
                'hyperpay_result_code' => $resultCode,
            ]);
        } elseif ($status === 'pending') {
            return response()->json([
                'status' => true,
                'message' => __('messages.deposit_processing'),
                'property' => __('messages.deposit_processing_property'),
                'deposit_status' => $deposit->status,
                'hyperpay_result_code' => $resultCode,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => __('messages.deposit_failed'),
                'property' => __('messages.deposit_failed_property'),
                'deposit_status' => $deposit->status,
                'hyperpay_result_code' => $resultCode,
            ], 400);
        }
    }
}
