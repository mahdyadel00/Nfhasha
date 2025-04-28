<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\CitiesResource;
use App\Http\Resources\API\DistrictsResource;
use App\Http\Resources\API\PickupTrucksResource;
use App\Http\Resources\API\SuccessResource;
use App\Models\City;
use App\Models\PickUpTruck;
use App\Models\TypePeriodicInspections;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function cities(Request $request)
    {
        $cities = City::get();

        return ApiResponse(200,
        __('messages.data_returned_successfully', ['attr' => __('messages.cities')]) ,
        CitiesResource::collection($cities)
        );
    }

    public function districts(Request $request, City $city)
    {
        $districts = $city->districts;

        return ApiResponse(200,
        __('messages.data_returned_successfully', ['attr' => __('messages.districts')]) ,
        DistrictsResource::collection($districts)
        );
    }

    public function pickupTrucks(Request $request)
    {
        $pickupTrucks = PickUpTruck::with('translations')->get();

        return ApiResponse(200,
        __('messages.data_returned_successfully', ['attr' => __('messages.pickup_trucks')]) ,
        PickupTrucksResource::collection($pickupTrucks)
        );
    }

    public function typePeriodicInspections()
    {
        $type_periodic_inspections = TypePeriodicInspections::get();

        return response()->json([
            'data' => $type_periodic_inspections
        ]);
    }

}
