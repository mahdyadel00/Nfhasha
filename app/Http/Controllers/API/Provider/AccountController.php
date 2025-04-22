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

    public function updateStatus(Request $request)
    {
        $user = auth()->user();
        $user->provider()->update(['status' => $request->status]);

        return new SuccessResource([
            'message' => __('messages.status_updated_successfully'),
        ]);
    }


    // public function updateStatus(Request $request)
    // {
    //     try {
    //         DB::beginTransaction();

    //         $provider = auth()->user();
    //         $status = $request->input('status'); // 'online' or 'offline'

    //         // Update provider status
    //         $provider->provider()->update(['status' => $status]);

    //         // If provider is online, send pending orders
    //         if ($status === 'online') {

    //             $service_type = [
    //                 'battery',
    //                 'towing',
    //                 'puncture',
    //                 'maintenance',
    //                 'comprehensive_inspections',
    //                 'periodic_inspections',
    //                 'car_reservations',
    //             ];
    //             // Fetch pending orders matching provider's service type
    //             $pendingOrders = Order::where('status', 'pending')
    //                 ->whereNull('provider_id')
    //                 ->whereIn('type', $service_type)
    //                 ->whereNotNull('from_lat')
    //                 ->whereNotNull('from_long')
    //                 ->get();

    //             foreach ($pendingOrders as $order) {
    //                 // Check if provider was already notified for this order
    //                 $alreadyNotified = ProviderNotification::where('order_id', $order->id)
    //                     ->where('provider_id', $provider->id)
    //                     ->exists();

    //                 if ($alreadyNotified) {
    //                     continue; // Skip if provider was already notified
    //                 }

    //                 // Check if provider is within 50km of order location
    //                 $isNearby = User::where('id', $provider->id)
    //                     ->whereNotNull('latitude')
    //                     ->whereNotNull('longitude')
    //                     ->nearby($order->from_lat, $order->from_long, 50)
    //                     ->exists();

    //                 if (!$isNearby) {
    //                     continue; // Skip if provider is not nearby
    //                 }

    //                 // Create notification record
    //                 ProviderNotification::create([
    //                     'user_id'       => $order->user_id,
    //                     'provider_id'   => $provider->id,
    //                     'order_id'      => $order->id,
    //                     'service_type'  => $order->type,
    //                     'message'       => __('messages.new_order'),
    //                     'order_status'  => $order->status,
    //                 ]);

    //                 // Define message based on service type
    //                 $message = match ($order->type) {
    //                     'battery'                   => __('messages.battery_service_request'),
    //                     'towing'                    => __('messages.tow_truck_service_request'),
    //                     'puncture'                  => __('messages.puncture_service_request'),
    //                     'maintenance'               => __('messages.maintenance_service_request'),
    //                     'comprehensive_inspections' => __('messages.comprehensive_inspection_service_request'),
    //                     'periodic_inspections'      => __('messages.periodic_inspection_service_request'),
    //                     'car_reservations'          => __('messages.car_reservations_service_request'),
    //                     default                     => __('messages.new_order_request'),
    //                 };

    //                 // Send Pusher notification
    //                 $pusher = new Pusher(
    //                     env('PUSHER_APP_KEY'),
    //                     env('PUSHER_APP_SECRET'),
    //                     env('PUSHER_APP_ID'),
    //                     ['cluster' => env('PUSHER_APP_CLUSTER'), 'useTLS' => true]
    //                 );

    //                 $pusher->trigger('notifications.providers.' . $provider->id, 'sent.offer', [
    //                     'message'       => $message,
    //                     'order'         => $order,
    //                     'order_status'  => $order->status,
    //                     'provider_id'   => $provider->id,
    //                 ]);

    //                 // Send Firebase notification if provider has FCM token
    //                 if (!empty($provider->fcm_token)) {
    //                     $firebaseService = new FirebaseService();

    //                     $extraData = [
    //                         'order_id'      => (string) $order->id,
    //                         'type'          => __('messages.new_order'),
    //                         'order_status'  => $order->status,
    //                         'sound'         => 'notify_sound',
    //                     ];

    //                     $firebaseService->sendNotificationToUser(
    //                         $provider->fcm_token,
    //                         __('messages.new_order'),
    //                         $message,
    //                         $extraData
    //                     );

    //                     Log::info('Pending order notification sent to provider', [
    //                         'order_id'    => $order->id,
    //                         'provider_id' => $provider->id,
    //                         'message'     => $message,
    //                     ]);
    //                 }
    //             }
    //         }

    //         DB::commit();

    //         // احصل على JsonResponse من الريسورس
    //         return (new SuccessResource([
    //             'message' => __('messages.status_updated_successfully'),
    //         ]))->response();

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         Log::channel('error')->error('Error updating provider status: ' . $e->getMessage(), [
    //             'provider_id' => auth()->id(),
    //             'status'      => $request->status,
    //         ]);

    //         // كذلك في حالة الخطأ
    //         return (new ErrorResource([
    //             'message' => __('messages.error_occurred'),
    //         ]))->response();
    //     }
    // }
}
