<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\ActivationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivationCodeController extends Controller
{
    public function activate(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $code = ActivationCode::where('code', $request->code)
            ->first();

        if (!$code) {
            return response()->json([
                'status' => false,
                'message' => __('messages.invalid_activation_code'),
                'property' => __('messages.invalid_activation_code_property')
            ], 400);
        }

        $user = Auth::user();

        // Check if user has already used this code
        if ($code->users()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'status' => false,
                'message' => __('messages.code_already_used'),
                'property' => __('messages.code_already_used_property')
            ], 400);
        }

        // Record code usage for this user
        $code->users()->attach($user->id, [
            'used_at' => now()
        ]);

        // Add balance to user
        $user->balance += $code->amount;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => __('messages.activation_code_applied'),
            'property' => __('messages.activation_code_applied_property'),
            'data' => [
                'amount' => $code->amount,
                'new_balance' => $user->balance
            ]
        ]);
    }

    // Admin method to generate codes
    public function generate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'count' => 'required|integer|min:1|max:100'
        ]);

        $codes = [];
        for ($i = 0; $i < $request->count; $i++) {
            $codes[] = ActivationCode::create([
                'code' => strtoupper(uniqid()),
                'amount' => $request->amount
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => __('messages.activation_codes_generated'),
            'property' => __('messages.activation_codes_generated_property'),
            'data' => $codes
        ]);
    }
}
