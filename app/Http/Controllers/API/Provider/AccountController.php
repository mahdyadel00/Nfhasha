<?php

namespace App\Http\Controllers\API\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\ChangePasswordRequest;
use App\Http\Requests\API\User\UpdateGeosRequest;
use App\Http\Requests\API\Provider\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AccountController extends Controller
{
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = auth('sanctum')->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return apiResponse(400, __('messages.old_password_is_incorrect'));
        }

        $user->update(['password' => $request->new_password]);

        return apiResponse(200 , __('messages.data_updated_successfully' , ['attr' => __('messages.Password')]));
    }

    public function logout()
    {
        $user = auth('sanctum')->user();

        $user->currentAccessToken()->delete();

        return apiResponse(200 , __('messages.logout_successfully'));
    }

    public function deleteAccount()
    {
        $user = auth()->user();

        $user->provider()->delete();

        $user->delete();

        return apiResponse(200 , __('messages.account_deleted_successfully'));
    }

    public function updateGeos(UpdateGeosRequest $request)
    {
        auth()->user()->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        return apiResponse(200 , __('messages.data_updated_successfully' , ['attr' => __('messages.Geos')]));
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        $sensitiveFields = [
            'mechanical', 'plumber', 'electrical', 'puncture', 'battery',
            'pickup', 'open_locks', 'full_examination', 'periodic_examination', 'truck_barriers'
        ];

        $provider = $user->provider;
        $accountSuspended = false;

        foreach ($sensitiveFields as $field) {
            if (!$provider->$field && $request->$field) {
                $accountSuspended = true;
                break;
            }
        }

        if ($accountSuspended) {
            $provider->is_active = false;
            $provider->save();

            return apiResponse(403, __('تم تعليق حسابك لحين المراجعة.'));
        }

        if ($user->phone !== $request->phone) {
            $user->email_verified_at = null;
            $user->otp = rand(100000, 999999);
        }

        if ($request->hasFile('profile_picture')) {
            $user->profile_picture = uploadImage($request->profile_picture, 'avatars');
        }

        $user->update($request->except(['profile_picture']));
        $provider->update($request->only($sensitiveFields));

        return apiResponse(200, __('messages.data_updated_successfully', ['attr' => __('messages.Profile')]), $user);
    }


}
