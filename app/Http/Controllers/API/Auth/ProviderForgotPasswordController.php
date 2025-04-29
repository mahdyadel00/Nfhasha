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

class ProviderForgotPasswordController extends Controller
{
    /**
     * Handle forgot password request and send OTP for providers.
     *
     * @param ForgetPassword $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgot(ForgetPassword $request)
    {
        try {
            DB::beginTransaction();

            // Find provider by phone number
            $provider = User::where('phone', $request->phone)->where('role', 'provider')->first();

            if (!$provider) {
                return new ErrorResource([
                    'message' => __('messages.provider_not_found'),
                ]);
            }

            // Generate a 6-digit OTP (between 100000 and 999999)
            $otp = mt_rand(100000, 999999);

            // Update provider with new OTP
            $provider->update([
                'otp' => (string) $otp,
                // 'otp_expires_at' => now()->addMinutes(10),
            ]);

            DB::commit();

            return new SuccessResource([
                'message' => __('messages.otp_sent_successfully'),
                'otp' => $provider->otp,
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
            $provider = User::where([
                'phone' => $request->phone,
                'otp' => $request->otp,
                'role' => 'provider',
            ])->first();

            if (!$provider) {
                return new ErrorResource([
                    'message' => __('messages.provider_not_found'),
                ]);
            }

            if ($provider->otp == $request->otp) {
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
            $provider = User::where('phone', $request->phone)->where('role', 'provider')->first();

            if (!$provider) {
                return new ErrorResource([
                    'message' => __('messages.provider_not_found'),
                ]);
            }

            $provider->update([
                'password' => Hash::make($request->password),
                'otp' => null,
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
