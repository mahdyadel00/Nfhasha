<?php

namespace App\Http\Controllers\API\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\Provider\PunctureServiceResource;
use App\Http\Resources\API\SuccessResource;
use App\Http\Resources\API\User\ExpressServiceResource;
use App\Models\ExpressService;
use App\Models\Order;
use App\Models\ProviderNotification;
use App\Models\PunctureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfferController extends Controller
{
    public function offers()
    {
        try {
            DB::beginTransaction();

            $provider_notifications = ProviderNotification::where('provider_id', auth()->id())->get();

            if ($provider_notifications->count() == 0) {
                return new ErrorResource([
                    'message' => 'No offers found',
                ]);
            }
            $express_services = PunctureService::whereIn('user_id', $provider_notifications->pluck('user_id')->toArray())
                ->where(function ($query) {
                    $query->where('status', 'pending') // حالة pending
                    ->orWhere(function ($query) {
                        $query->where('status', 'sent') // حالة sent
                        ->where('provider_id', auth()->id()); // فقط إذا كنت أنت من غير الحالة
                    });
                })
                ->whereHas('user', function ($query) {
                    $query->where('role', 'provider'); // التحقق من أن المستخدم لديه دور provider
                })
                ->orderBy('created_at', 'desc')
                ->get();

            DB::commit();

            return new SuccessResource([
                'express_services' => PunctureServiceResource::collection($express_services->map(function ($service) {
                    $isSentByMe = $service->provider_id === auth()->id();
                    $service->status = $isSentByMe ? 'sent' : $service->status;
                    return $service;
                })),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error('Error in OfferController@offers: ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage()]);
        }
    }

    public function offer($id)
    {
        try{
            DB::beginTransaction();

            $express_service = PunctureService::where('id', $id)->first();

            DB::commit();

            return new SuccessResource([
                'express_service' => new PunctureServiceResource($express_service),
            ]);

        }catch (\Exception $e){
            DB::rollBack();
            Log::channel('error')->error('Error in OfferController@offer: ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage(),]);
        }
    }

    public function acceptOffer($id)
    {
        try {
            DB::beginTransaction();

            $express_service = PunctureService::find($id);

            if (!$express_service) {
                return new ErrorResource(['message' => 'Offer not found']);
            }

            if ($express_service->status !== 'pending') {
                return new ErrorResource(['message' => 'Offer already accepted']);
            }

            $express_service->status = 'accepted';
            $express_service->save();

            $order = Order::where('express_service_id', $express_service->express_service_id)
                ->where('status', 'pending')
                ->first();

            if ($order) {
                $order->status      = 'accepted';
                $order->provider_id = auth()->id();
                $order->save();
            }

            DB::commit();

            event(new \App\Events\ProviderNotification('Offer accepted', [auth()->id()], $express_service));

            return new SuccessResource(['message' => 'Offer accepted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error('Error in OfferController@acceptOffer: ' . $e->getMessage());
            return new ErrorResource(['message' => 'Something went wrong, please try again later']);
        }
    }


    public function sendOffer(Request $request , $id)
    {
        try{
            DB::beginTransaction();

            $express_service = PunctureService::where(['id' => $id, 'status' => 'pending'])->first();


            if(!$express_service || $express_service->status != 'pending'){
                return new ErrorResource([
                    'message' => 'Offer not found or already sent',
                ]);
            }

            $express_service->update([
                'status'        => 'sent',
                'amount'        => $request->amount,
                'provider_id'   => auth()->id(),
            ]);

            $order = Order::where('user_id', $express_service->user_id)
                ->where('status', 'pending')
                ->first();

            $order->status = 'sent';
            $order->save();


            //send notification to user
            Broadcast(new \App\Events\SentOffer('Offer sent',auth()->id(), $express_service, $request->amount));

            DB::commit();

            return new SuccessResource([
                'message' => 'Offer sent successfully',
            ]);

        }catch (\Exception $e){
            DB::rollBack();
            Log::channel('error')->error('Error in OfferController@sendOffer: ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage(),]);
        }
    }

    public function rejectOffer($id)
    {
        try{
            DB::beginTransaction();

            $express_service = PunctureService::where(['id' => $id, 'status' => 'pending'])->first();
            if(!$express_service || $express_service->status != 'pending'){
                return new ErrorResource([
                    'message' => 'Offer not found or already rejected',
                ]);
            }
            $express_service->update([
                'status'        => 'pending',
                'provider_id'   => null,
            ]);

            DB::commit();

            //send notification to user
            Broadcast(new \App\Events\ProviderNotification('Offer rejected', [auth()->id()], $express_service));

            return new SuccessResource([
                'message' => 'Offer rejected successfully',
            ]);

        }catch (\Exception $e){
            DB::rollBack();
            Log::channel('error')->error('Error in OfferController@rejectOffer: ' . $e->getMessage());
            return new ErrorResource(['message' => $e->getMessage(),]);
        }
    }
}
