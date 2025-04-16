<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ServiceOfferResouce;
use App\Http\Resources\API\SuccessResource;
use App\Http\Resources\API\ErrorResource;
use App\Models\ServiceOffer;
use Illuminate\Http\Request;

class ServiceOfferController extends Controller
{
    public function index()
    {
        try {
            $service_offers = ServiceOffer::with(['service', 'translations'])
                ->where('status', 'active')
                ->paginate(config('app.paginate'));

            if ($service_offers->isEmpty()) {
                return new SuccessResource([
                    'message' => __('messages.no_service_offers_found'),
                    'data' => []
                ]);
            }

            return new SuccessResource([
                'message' => __('messages.data_returned_successfully', ['attr' => __('messages.service_offers')]),
                'data' => ServiceOfferResouce::collection($service_offers),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in ServiceOfferController@index: ' . $e->getMessage());
            return new ErrorResource(__('messages.something_went_wrong'));
        }
    }

    public function show($id)
    {
        try {
            $service_offer = ServiceOffer::with(['service', 'translations'])
                ->where('status', 'active')
                ->findOrFail($id);

            return new SuccessResource([
                'message' => __('messages.data_returned_successfully', ['attr' => __('messages.service_offers')]),
                'data' => ServiceOfferResouce::make($service_offer),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in ServiceOfferController@show: ' . $e->getMessage());
            return new ErrorResource(__('messages.something_went_wrong'));
        }
    }

    public function coupon(Request $request, $id)
    {

        $request->validate([
            'coupon_code' => 'required|string|exists:service_offers,code',
        ]);

        $service_offer = ServiceOffer::findOrFail($id);

        $coupon = ServiceOffer::where('code', $request->coupon_code)->first();

        if (!$coupon) {
            return response()->json([
                'message' => __('messages.invalid_or_expired_coupon'),
            ], 400);
        }
        $discounted_price = $service_offer->service->price - ($coupon->price / 100);

        return new SuccessResource([
            'message' => __('messages.data_returned_successfully', ['attr' => __('messages.coupon')]),
            'data'    => [
                'original_price'    => $service_offer->price,
                'discounted_price'  => $discounted_price,
            ]
        ]);

    }

}
