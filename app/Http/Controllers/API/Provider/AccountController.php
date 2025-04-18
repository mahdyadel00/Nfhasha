<?php

namespace App\Http\Controllers\API\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\ChangePasswordRequest;
use App\Http\Requests\API\User\UpdateGeosRequest;
use App\Http\Requests\API\Provider\UpdateProfileRequest;
use App\Http\Resources\API\SuccessResource;
use App\Http\Resources\API\User\UserResource;
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

        return apiResponse(200, __('messages.data_updated_successfully', ['attr' => __('messages.Password')]));
    }

    public function logout()
    {
        $user = auth('sanctum')->user();

        $user->currentAccessToken()->delete();

        return apiResponse(200, __('messages.logout_successfully'));
    }

    public function deleteAccount()
    {
        $user = auth()->user();

        $user->provider()->delete();

        $user->delete();

        return apiResponse(200, __('messages.account_deleted_successfully'));
    }

    public function updateGeos(UpdateGeosRequest $request)
    {
        auth()->user()->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        return apiResponse(200, __('messages.data_updated_successfully', ['attr' => __('messages.Geos')]));
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth('sanctum')->user();
        $old_phone = $user->phone;

        $user->update([
            'name'                              => $request->name,
            'phone'                             => $request->phone,
            'email'                             => $request->email,
            'role'                              => 'provider',
            'address'                           => $request->address,
            'otp'                              => random_int(000000, 999999),
            'email_verified_at'                => null,
        ]);

        if ($old_phone !== $request->phone) {
            $user->update([
                'otp' => random_int(000000, 999999),
            ]);

            return new SuccessResource([
                'message' => __('messages.otp_required_for_phone_verification'),
                'data'    => $user->otp,
            ]);
        } else {
            $user->provider()->update([
                'is_active' => 0,
            ]);
        }
        if ($request->hasFile('profile_picture')) {
            $user->profile_picture = uploadImage($request->profile_picture, 'avatars');
            $user->save();
        }


        $user->provider()->update([
            'city_id'                   => $request->city_id,
            'district_id'               => $request->district_id,
            'type'                      => $request->type,
            'mechanical'                => $request->mechanical,
            'plumber'                   => $request->plumber,
            'electrical'                => $request->electrical,
            'puncture'                  => $request->puncture,
            'tow_truck'                 => $request->tow_truck,
            'battery'                   => $request->battery,
            'fuel'                      => $request->fuel,
            'pickup'                    => $request->pickup,
            'open_locks'                => $request->open_locks,
            'periodic_inspections'      => $request->periodic_inspections,
            'comprehensive_inspections' => $request->comprehensive_inspections,
            'maintenance'               => $request->maintenance,
            'car_reservations'          => $request->car_reservations,
            'pick_up_truck_id'          => $request->wenchId,
            'available_from'            => $request->truck_barriers_from,
            'available_to'              => $request->truck_barriers_to,
            'home_service'              => $request->home_service,
            'commercial_register'       => $request->file('commercial_register') ? uploadImage($request->file('commercial_register'), 'providers/commercial_registers') : null,
            'owner_identity'            => $request->file('owner_identity') ? uploadImage($request->file('owner_identity'), 'providers/owner_identities') : null,
            'general_license'           => $request->file('general_license') ? uploadImage($request->file('general_license'), 'providers/general_licenses') : null,
            'municipal_license'         => $request->file('municipal_license') ? uploadImage($request->file('municipal_license'), 'providers/municipal_licenses') : null,

        ]);

        return new SuccessResource([
            'message'   => __('messages.data_updated_successfully', ['attr' => __('messages.Profile')]),
        ]);
    }

    public function profile()
    {
        $provider = auth()->user();
        // check if provider
        if (!$provider->provider) {
            return new SuccessResource([
                'message' => __('messages.provider_not_found'),
            ]);
        }

        $provider->provider->is_active == 0 ? $provider->provider->update(['is_active' => 1]) : null;

        return new SuccessResource([
            'data' => UserResource::make(auth()->user())
        ]);
    }

    public function resendOtp()
    {
        $user = auth()->user();

        $user->update([
            'otp' => random_int(000000, 999999),
        ]);

        return new SuccessResource([
            'message' => __('messages.otp_sent_successfully'),
            'data'    => $user->otp,
        ]);
    }

    public function fcmToken(Request $request)
    {
        $user = auth()->user();
        $user->update(['fcm_token' => $request->fcm_token]);

        return new SuccessResource(__('messages.fcm_token_updated_successfully'));
    }
}
