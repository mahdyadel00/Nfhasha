<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\ForgetPassword;
use App\Http\Requests\API\Auth\ResetPassword;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\SuccessResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    /**
     * Handle forgot password request and send OTP.
     *
     * @param ForgetPassword $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgot(ForgetPassword $request)
    {
        try {
            DB::beginTransaction();

            // Find user by phone number
            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                return new ErrorResource([
                    'message' => __('messages.user_not_found'),
                ]);
            }

            // Check if the user is a provider
            if ($user->role !== 'user') {
                // Assuming 'role' differentiates user and provider
                return new ErrorResource([
                    'message' => __('messages.not_allowed_for_providers'),
                ]);
            }

            // Generate a 6-digit OTP (between 100000 and 999999)
            $otp = mt_rand(100000, 999999);

            // Update user with new OTP and reset email verification
            $user->update([
                'otp' => (string) $otp,
                // 'otp_expires_at' => now()->addMinutes(10),
                'email_verified_at' => null,
            ]);

            DB::commit();

            return new SuccessResource([
                'message' => __('messages.otp_sent_successfully'),
                'otp' => $user->otp,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error with context
            Log::channel('error')->error(__('messages.forgot_password_error') . ': ' . $e->getMessage(), ['phone' => $request->phone]);

            return new ErrorResource([
                'message' => __('messages.forgot_password_error'),
            ]);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $user = User::where([
                'phone' => $request->phone,
                'otp' => $request->otp,
            ])->first();

            if (!$user) {
                return new ErrorResource([
                    'message' => __('messages.user_not_found'),
                ]);
            }

            // Check if the user is a provider
            if ($user->role !== 'user') {
                return new ErrorResource([
                    'message' => __('messages.not_allowed_for_providers'),
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
            Log::channel('error')->error(__('messages.verify_otp_error') . ': ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage()]);
        }
    }

    public function reset(ResetPassword $request)
    {
        try {
            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                return new ErrorResource([
                    'message' => __('messages.user_not_found'),
                ]);
            }

            // Check if the user is a provider
            if ($user->role !== 'user') {
                return new ErrorResource([
                    'message' => __('messages.not_allowed_for_providers'),
                ]);
            }

            $user->update([
                'password' => Hash::make($request->password),
                'otp' => null,
                'email_verified_at' => now(),
            ]);
            return new SuccessResource([
                'message' => __('messages.password_reset_successfully'),
            ]);
        } catch (\Exception $e) {
            Log::channel('error')->error(__('messages.reset_password_error') . ': ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage()]);
        }
    }
}
