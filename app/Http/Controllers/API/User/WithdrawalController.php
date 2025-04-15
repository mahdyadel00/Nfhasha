<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_holder' => 'required|string|max:50',
            'iban' => 'required|string|max:50',
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'error' => __('messages.login_first'),
                'property_message' => __('messages.login_first_property')
            ], 401);
        }

        if ($user->balance < $request->amount) {
            return response()->json([
                'error' => __('messages.insufficient_balance'),
                'property_message' => __('messages.insufficient_balance_property')
            ], 400);
        }

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'bank_name' => $request->bank_name,
            'account_holder' => $request->account_holder,
            'iban' => $request->iban,
            'amount' => $request->amount,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => __('messages.withdrawal_request_sent'),
            'property_message' => __('messages.withdrawal_request_sent_property'),
            'withdrawal' => $withdrawal,
        ]);
    }
}
