<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\ChangePasswordRequest;
use App\Http\Requests\API\User\UpdateProfileRequest;
use App\Http\Requests\API\User\UpdateGeosRequest;
use App\Http\Resources\API\User\NotificationsResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
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
        $user = auth()->user();

        $user->currentAccessToken()->delete();

        return apiResponse(200 , __('messages.logout_successfully'));
    }

    public function deleteAccount()
    {
        $user = auth()->user();

        $user->delete();

        return apiResponse(200 , __('messages.account_deleted_successfully'));
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        if($user->phone != $request->phone) {
            $user->email_verified_at = null;
            $user->otp = rand(100000, 999999);
        }

        if($request->hasFile('profile_picture')) {
            $user->profile_picture = uploadImage($request->profile_picture , 'avatars');
        }

        $user->update($request->except('profile_picture'));

        return apiResponse(200 , __('messages.data_updated_successfully' , ['attr' => __('messages.Profile')]) , $user);
    }

    public function notifications()
    {
        $notifications = auth()->user()->notifications;

        return apiResponse(200 , __('messages.data_returned_successfully' , ['attr' => __('messages.notifications')]) , NotificationsResource::collection($notifications));
    }

    public function notification($notification)
    {
        $notification = auth()->user()->notifications()->where('id' , $notification)->first();

        if (!$notification) {
            return apiResponse(404 , __('messages.notification_not_found'));
        }

        $notification->markAsRead();

        return apiResponse(200 , __('messages.data_returned_successfully' , ['attr' => __('messages.notification')]) , new NotificationsResource($notification));
    }

    public function updateGeos(UpdateGeosRequest $request)
    {
        auth()->user()->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        return apiResponse(200 , __('messages.data_updated_successfully' , ['attr' => __('messages.Geos')]));
    }
}
