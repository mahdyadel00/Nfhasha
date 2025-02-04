<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\SuccessResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    public function forgot(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                return new ErrorResource([
                    'message' => __('messages.user_not_found'),
                ]);
            }
            $user->update([
                'otp' => random_int(000000, 999999),
            ]);


            DB::commit();
            return new SuccessResource([
                'message'   => __('messages.otp_sent_successfully'),
                'otp'       => $user->otp,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error('forgot password error: ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage(),]);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $user = User::where([
                'phone' => $request->phone,
                'otp'   => $request->otp,
            ])->first();
            if (!$user) {
                return new ErrorResource([
                    'message' => __('messages.user_not_found'),
                ]);
            }
            if ($user->otp == $request->otp) {
                return new SuccessResource([
                    'message' => __('messages.otp_verified_successfully'),
                ]);
            }
            return new ErrorResource([
                'message' => __('messages.otp_not_verified'),
            ]);
        } catch (\Exception $e) {
            Log::channel('error')->error('verify otp error: ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage(),]);
        }
    }

    public function reset(Request $request)
    {
        try {
            $user = User::where('phone', $request->phone)->first();
            if (!$user) {
                return new ErrorResource([
                    'message' => __('messages.user_not_found'),
                ]);
            }
            $user->update([
                'password' => Hash::make($request->password),
            ]);
            return new SuccessResource([
                'message' => __('messages.password_reset_successfully'),
            ]);
        } catch (\Exception $e) {
            Log::channel('error')->error('reset password error: ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage(),]);
        }
    }
}
