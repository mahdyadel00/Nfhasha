<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ServiceOfferResouce;
use App\Http\Resources\API\SuccessResource;
use App\Models\ServiceOffer;
use Illuminate\Http\Request;

class ServiceOfferController extends Controller
{
    public function index(){

        $service_offers = ServiceOffer::paginate(config('app.paginate'));

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully' , ['attr' => __('messages.service_offers')]) ,
            'data'      =>  ServiceOfferResouce::collection($service_offers),
        ]);
    }

    public function show($id){
        $service_offer = ServiceOffer::find($id);

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully' , ['attr' => __('messages.service_offers')]) ,
        'data'      =>  ServiceOfferResouce::make($service_offer),
        ]);

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