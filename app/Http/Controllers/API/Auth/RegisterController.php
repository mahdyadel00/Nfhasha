<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\ProviderRegisterRequest;
use App\Http\Requests\API\Auth\RegisterRequest;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\SuccessResource;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\ActivationCode;

class RegisterController extends Controller
{
    public $locale;

    public function __construct()
    {
        $this->locale = request()->header('Accept-Language', config('app.locale'));
    }
    public function user(RegisterRequest $request)
    {
        $user = User::where('phone', $request->phone)->where('role', 'user')->first();
        if ($user) {
            return new ErrorResource([
                'message' => __('messages.phone_already_used'),
            ]);
        }
        $user = User::create([
            'name'          => $request->name,
            'phone'         => $request->phone,
            'password'      => Hash::make($request->password),
            'longitude'     => $request->longitude,
            'latitude'      => $request->latitude,
            'address'       => $request->address,
            'fcm_token'     => $request->fcm_token ?? null,
        ]);

        if ($request->has('activation_code')) {
            $code = ActivationCode::where('code', $request->activation_code)->first();

            if ($code) {
                // التحقق مما إذا كان رقم الهاتف قد استخدم رمز تفعيل من قبل
                $phoneUsedCode = ActivationCode::whereHas('user', function ($query) use ($user) {
                    $query->where('phone', $user->phone);
                })->exists();

                if ($phoneUsedCode) {
                    // إذا كان رقم الهاتف قد استخدم رمز تفعيل، أعد رسالة خطأ
                    return new ErrorResource([
                        'message' => __('messages.phone_already_used_code'),
                    ]);
                }

                // التحقق مما إذا كان الرمز مستخدم من قبل (is_used أو user_id)
                if (!$code->is_used && !$code->user_id) {
                    $user->balance += $code->amount;
                    $user->save();

                    // ربط الرمز بالمستخدم وتحديث الحالة
                    $code->update([
                        'user_id'  => $user->id,
                        'is_used'  => true,
                        'used_at'  => now(),
                    ]);
                } else {
                    // إذا كان الرمز مستخدم بالفعل
                    return new ErrorResource([
                        'message' => __('messages.code_already_used'),
                    ]);
                }
            } else {
                // إذا كان الرمز غير صالح
                return new ErrorResource([
                    'message' => __('messages.invalid_activation_code'),
                ]);
            }
        }

        if ($request->has('invitation_code')) {
            $inviter = User::where('invitation_code', $request->invitation_code)->first();
            if ($inviter) {
                $inviter->invitations()->create(['invited_user_id' => $user->id]);
            }
        }

        $token = $user->createToken('auth_token', ['role' => 'user'])->plainTextToken;

        return new SuccessResource([
            'message' => __('messages.registered_successfully'),
            'data'    => [
                'otp'   => str($user->otp),
                'token' => $token,
            ]
        ]);
    }
    public function provider(ProviderRegisterRequest $request)
    {
        $user = User::where('phone', $request->phone)->where('role', 'provider')->first();
        if ($user) {
            return new ErrorResource([
                'message' => __('messages.phone_already_used'),
            ]);
        }

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
                'fcm_token'             => $request->fcm_token ?? null,
            ]
        );

        $user->provider()->create(
            [
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
                'maintenance'               => 1,
                'car_reservations'          => $request->car_reservations,
                'pick_up_truck_id'          => $request->wenchId,
                'available_from'            => $request->truck_barriers_from,
                'available_to'              => $request->truck_barriers_to,
                'home_service'              => $request->home_service,
                'commercial_register'       => $request->file('commercial_register') ? uploadImage($request->file('commercial_register'), 'providers/commercial_registers') : null,
                'owner_identity'            => $request->file('owner_identity') ? uploadImage($request->file('owner_identity'), 'providers/owner_identities') : null,
                'general_license'           => $request->file('general_license') ? uploadImage($request->file('general_license'), 'providers/general_licenses') : null,
                'municipal_license'         => $request->file('municipal_license') ? uploadImage($request->file('municipal_license'), 'providers/municipal_licenses') : null,

            ]
        );

        $token = $user->createToken('auth_token', ['role' => 'provider'])->plainTextToken;

        // if (!empty($user->fcm_token)) {
        //     $firebaseService = new FirebaseService();
        //     $firebaseService->sendNotificationToUser(
        //         $user->fcm_token,
        //         'Welcome to ' . config('app.name'),
        //         'Welcome to ' . config('app.name')
        //     );
        // }


        return new SuccessResource([
            'message'     => __('messages.registered_successfully'),
            'data'        => [
                'otp'       => str($user->otp),
                'token'     => $token,
            ]
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

        return apiResponse(200, __('messages.otp_sent_successfully'), ['otp' => str($otp)]);
    }

    public function resendOtp(Request $request)
    {
        $user = auth('sanctum')->user();

        $otp = rand(100000, 999999);

        $user->update([
            'otp'               => $otp,
            'email_verified_at' => null,
        ]);

        return new SuccessResource([
            'message' => __('messages.otp_sent_successfully'),
            'data'    => $user->otp,
        ]);
    }

    public function terms()
    {
        $terms = settings()->get('terms_and_conditions_' . $this->locale);


        return apiResponse(
            200,
            __('messages.data_returned_successfully', ['attr' => __('messages.terms')]),
            [
                'terms' => $terms
            ]
        );
    }

    public function getFirebaseToken()
    {
        $firebaseService = new FirebaseService();
        $token = $firebaseService->getAccessToken();

        return response()->json([
            'message' => __('messages.data_returned_successfully', ['attr' => __('messages.firebase_token')]),
            'access_token' => $token
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|unique:users',
            'activation_code' => 'nullable|string'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        if ($request->activation_code) {
            $code = ActivationCode::where('code', $request->activation_code)->first();

            if ($code) {
                if (!$code->users()->where('user_id', $user->id)->exists()) {
                    $user->balance += $code->amount;
                    $user->save();

                    $code->users()->attach($user->id, [
                        'used_at' => now()
                    ]);
                }
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => __('messages.user_registered'),
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }
}
