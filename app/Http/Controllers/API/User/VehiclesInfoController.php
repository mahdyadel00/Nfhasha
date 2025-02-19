<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\SuccessResource;
use App\Http\Resources\API\VehiclesInfo\BrandsResource;
use App\Http\Resources\API\VehiclesInfo\ModelsResource;
use App\Http\Resources\API\VehiclesInfo\TypesResource;
use App\Http\Resources\API\VehiclesInfo\YearsResource;
use App\Models\VehicleBrand;
use App\Models\VehicleManufactureYear;
use App\Models\VehicleModel;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehiclesInfoController extends Controller
{
    public function years()
    {
        $years = VehicleManufactureYear::active()->get();

        return apiResponse(200,
    __('messages.data_returned_successfully', ['attr' => __('messages.years')]),
         YearsResource::collection($years));
    }

    public function types(Request $request)
    {
        $types = VehicleType::active()->get();

        return apiResponse(200,
        __('messages.data_returned_successfully', ['attr' => __('messages.types')]),
         TypesResource::collection($types));
    }

    public function brands(Request $request)
    {

        $brands = VehicleBrand::active()->where('vehicle_type_id', $request->type_id)->get();

        return new SuccessResource(__('messages.data_returned_successfully', ['attr' => __('messages.brands')]));
    }

    public function models(Request $request)
    {
        $data = VehicleModel::query()->active();

        if($request->has('brand_id')){
            $data->where('vehicle_brand_id', $request->brand_id);
        }

        $models = $data->get();

        return apiResponse(200,
        __('messages.data_returned_successfully', ['attr' => __('messages.models')]),
         ModelsResource::collection($models));
    }
}
