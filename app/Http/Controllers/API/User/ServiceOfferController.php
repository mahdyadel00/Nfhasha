<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ServiceOfferResouce;
use App\Http\Resources\API\SuccessResource;
use App\Models\ServiceOffer;

class ServiceOfferController extends Controller
{
    public function index(){

        $service_offers = ServiceOffer::get();

        return new SuccessResource([
            'message'   => __('messages.data_returned_successfully' , ['attr' => __('messages.service_offers')]) ,
            'data'      =>  ServiceOfferResouce::collection($service_offers),
        ]);
    }
}
