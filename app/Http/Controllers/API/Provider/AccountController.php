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
        $user = auth('sanctum')->user();

         $user->update([
            'name'                              => $request->name,
            'phone'                             => $request->phone,
            'email'                             => $request->email,
            'role'                              => 'provider',
            'address'                           => $request->address,
        ]);


        if ($request->hasFile('profile_picture')) {
            $user->profile_picture = uploadImage($request->profile_picture, 'avatars');
        }

        $user->provider()->update([
            'city_id'                   => $request->city_id,
            'district_id'               => $request->district_id,
            'pick_up_truck_id'          => $request->pick_up_truck_id,
            'type'                      => $request->type,
            'mechanical'                => $request->mechanical,
            'plumber'                   => $request->plumber,
            'electrical'                => $request->electrical,
            'puncture'                  => $request->puncture,
            'battery'                   => $request->battery,
            'pickup'                    => $request->pickup,
            'open_locks'                => $request->open_locks,
            'full_examination'          => $request->full_examination,
            'periodic_examination'      => $request->periodic_examination,
            'truck_barriers'            => $request->truck_barriers,
            'available_from'            => $request->available_from,
            'available_to'              => $request->available_to,
            'home_service'              => $request->home_service,
            'commercial_register'       => $request->commercial_register,
            'owner_identity'            => $request->owner_identity,
            'general_license'           => $request->general_license,
            'municipal_license'         => $request->municipal_license,
            'is_active'                 => 0,
        ]);

        return new SuccessResource(__('messages.data_updated_successfully' , ['attr' => __('messages.Profile')]));
    }

    public function profile()
    {
        return new SuccessResource([
            'data' => UserResource::make(auth()->user())
        ]);
    }

}
