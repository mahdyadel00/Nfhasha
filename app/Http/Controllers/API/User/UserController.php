<?php

namespace App\Http\Controllers\API\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\SuccessResource;
use App\Http\Resources\API\User\UserResource;
use App\Http\Requests\API\User\UpdateGeosRequest;
use App\Http\Requests\API\User\UpdateProfileRequest;
use App\Http\Requests\API\User\ChangePasswordRequest;
use App\Http\Resources\API\User\NotificationsResource;

class UserController extends Controller
{
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            if (!Hash::check($request->old_password, $user->password)) {
                return new ErrorResource([
                    'message' => __('messages.old_password_is_incorrect')
                ]);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            DB::commit();

            return new SuccessResource([
                'message' => __('messages.password_changed_successfully')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error(__('messages.error_in_change_password') . ': ' . $e->getMessage());
            return new ErrorResource(['message' => __('messages.error_occurred')]);
        }
    }

    public function logout()
    {
        $user = auth()->user();

        $user->currentAccessToken()->delete();

        return apiResponse(200, __('messages.logout_successfully'));
    }

    public function deleteAccount()
    {
        $user = auth()->user();

        $user->delete();

        return apiResponse(200, __('messages.account_deleted_successfully'));
    }

    public function profile()
    {
        return new SuccessResource([
            'data' => UserResource::make(auth()->user())
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        if ($user->phone     != $request->phone) {
            $user->email_verified_at = null;
            $user->otp      = rand(100000, 999999);
        }

        if ($request->hasFile('profile_picture')) {
            $user->profile_picture = uploadImage($request->profile_picture, 'avatars');
        }

        $user->update($request->except('profile_picture'));

        return new SuccessResource([
            'message' => __('messages.profile_updated_successfully'),
            'otp'     => $user->otp
        ]);
    }

    public function notifications()
    {
        $notifications = auth()->user()->notifications;

        return apiResponse(200, __('messages.data_returned_successfully', ['attr' => __('messages.notifications')]), NotificationsResource::collection($notifications));
    }

    public function notification($notification)
    {
        $notification = auth()->user()->notifications()->where('id', $notification)->first();

        if (!$notification) {
            return apiResponse(404, __('messages.notification_not_found'));
        }

        $notification->markAsRead();

        return apiResponse(200, __('messages.data_returned_successfully', ['attr' => __('messages.notification')]), new NotificationsResource($notification));
    }

    public function updateGeos(UpdateGeosRequest $request)
    {
        auth()->user()->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        return apiResponse(200, __('messages.data_updated_successfully', ['attr' => __('messages.Geos')]));
    }

    public function fcmToken(Request $request)
    {
        $user = auth()->user();
        $user->update(['fcm_token' => $request->fcm_token]);

        return new SuccessResource(__('messages.fcm_token_updated_successfully'));
    }
}
