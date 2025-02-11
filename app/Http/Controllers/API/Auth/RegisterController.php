<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Requests\API\Auth\ProviderRegisterRequest;
use App\Http\Requests\API\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function user(RegisterRequest $request)
    {
        $user = User::create([
            'name'          => $request->name,
            'phone'         => $request->phone,
            'password'      => Hash::make($request->password),
            'longitude'     => $request->longitude,
            'latitude'      => $request->latitude,
            'address'       => $request->address,
        ]);


        if ($request->has('invitation_code')) {
            $inviter = User::where('invitation_code', $request->invitation_code)->first();
            $inviter->invitations()->create(['invited_user_id' => $user->id]);
        }

        $token = $user->createToken('auth_token' , ['role' => 'user'])->plainTextToken;


        return apiResponse(201,
        __('messages.registered_successfully'),
        [
            'otp'       => str($user->otp),
            'token'     => $token,
            'user'      => $user,
        ]);
    }

    public function provider(ProviderRegisterRequest $request)
    {

        $user = User::create(
            [
            'name'                  => $request->name,
            'phone'                 => $request->phone,
            'password'              => $request->password,
            'address'               => $request->location,
            'longitude'             => $request->longitude,
            'latitude'              => $request->latitude,
            'email'                 => $request->email,
            'role'                  => 'provider',
        ]);

        $user->provider()->create(
            [
                'city_id'                   => $request->city_id,
                'district_id'               => $request->district_id,
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
                'pick_up_truck_id'          => $request->pick_up_truck_id,
                'available_from'            => $request->available_from,
                'available_to'              => $request->available_to,
                'home_service'              => $request->home_service,
                'commercial_register'       => $request->file('commercial_register') ? uploadImage($request->file('commercial_register'), 'providers/commercial_registers') : null,
                'owner_identity'            => $request->file('owner_identity') ? uploadImage($request->file('owner_identity'), 'providers/owner_identities') : null,
                'general_license'           => $request->file('general_license') ? uploadImage($request->file('general_license'), 'providers/general_licenses') : null,
                'municipal_license'         => $request->file('municipal_license') ? uploadImage($request->file('municipal_license'), 'providers/municipal_licenses') : null,
            ]
        );

        $token = $user->createToken('auth_token' , ['role' => 'provider'])->plainTextToken;

        return apiResponse(201,
        __('messages.registered_successfully'),
        [
            'otp' => str($user->otp),
            'token' => $token,
            'user' => $user,
        ]);
    }


    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|min_digits:6|max_digits:6',
        ]);

        $user = auth('sanctum')->user();

        if ($user->otp != $request->otp) {
            return apiResponse(422, __('messages.invalid_otp'));
        }

        $user->update([
                'email_verified_at' => now(),
                'otp' => null,
            ]);

        return apiResponse(200, __('messages.verified_successfully'));
    }

    public function sendOtp(Request $request)
    {
        $user = auth('sanctum')->user();

        $otp = rand(100000, 999999);

        $user->update(['otp' => $otp]);

        return apiResponse(200, __('messages.otp_sent_successfully') , ['otp' => str($otp)]);
    }

    public function resendOtp(Request $request)
    {
        $user = auth('sanctum')->user();

        $otp = rand(100000, 999999);

        $user->update(['otp' => $otp]);

        return apiResponse(200, __('messages.otp_sent_successfully') , ['otp' => str($otp)]);
    }

}
