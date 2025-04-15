<?php

namespace App\Http\Controllers\API\Auth;

use App\Services\FirebaseService;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\SuccessResource;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Resources\API\User\UserResource;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function user(LoginRequest $request)
    {
        if (auth()->attempt($request->only('phone', 'password'))) {
            $user = auth()->user();

            $user->update([
                'longitude'         => $request->longitude,
                'latitude'          => $request->latitude,
                'fcm_token'         => $request->fcm_token ?? null,
            ]);

            if ($user->role === 'user') {
                $token = $user->createToken('auth_token')->plainTextToken;
                if (!$user->email_verified_at) {
                    return apiResponse(
                        401,
                        __('messages.not_verified'),
                        [
                            'token' => $token,
                            'user'  => $user
                        ]
                    );
                }

                return new SuccessResource([
                    'token' => $token,
                    'data' => UserResource::make(auth()->user())
                ]);
            } else {
                return apiResponse(403, __('messages.invalid_credentials'));
            }
        }


        return apiResponse(401, __('messages.invalid_credentials'));
    }


    public function provider(LoginRequest $request)
    {
        if (auth()->attempt($request->only('phone', 'password'))) {
            $user = auth()->user();

            $user->update([
                'longitude'     => $request->longitude,
                'latitude'      => $request->latitude,
                'fcm_token'     => $request->fcm_token ?? null,
            ]);

            if ($user->role === 'provider') {
                if (!$user->provider->is_active) {
                    return apiResponse(401, __('messages.pending_approval'));
                }

                $user->tokens()->delete();

                $token = $user->createToken('auth_token')->plainTextToken;
                return new SuccessResource([
                    'token' => $token,
                    'data' => UserResource::make(auth()->user())
                ]);
            } else {
                return apiResponse(401, __('messages.invalid_credentials'));
            }
        }

        return apiResponse(401, __('messages.invalid_credentials'));
    }
}
