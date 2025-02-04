<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\CreateVehicleRequest;
use App\Http\Requests\API\User\UpdateVehicleRequest;
use App\Http\Resources\API\User\VehiclesResource;
use App\Models\UserVehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;
class VehiclesController extends Controller
{
    public function index()
    {
        $vehicles = auth()->user()->vehicles;

        return apiResponse( 200 ,
        __('messages.data_returned_successfully', ['attr' => __('messages.vehicles')]) ,
        VehiclesResource::collection($vehicles)
    );
    }

    public function store(CreateVehicleRequest $request)
    {
        $this->authorize('create', UserVehicle::class);

        $request['checkup_date'] = Carbon::createFromFormat('d-m-Y', $request->checkup_date)->format('Y-m-d');

        $vehicle = auth()->user()->vehicles()->create($request->except('images'));

        foreach ($request->images as $image) {
            $vehicle->images()->create([
                'path' => uploadImage($image, 'users/vehicles')
            ]);
        }

        return apiResponse( 201 ,
        __('messages.data_created_successfully', ['attr' => __('messages.vehicle')]) ,
        new VehiclesResource($vehicle)
        );

    }

    public function show(UserVehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        $vehicle = auth()->user()->vehicles()->findOrFail($vehicle->id);

        return apiResponse( 200 ,
        __('messages.data_returned_successfully', ['attr' => __('messages.vehicle')]) ,
        new VehiclesResource($vehicle)
        );
    }

    public function update(UserVehicle $vehicle , UpdateVehicleRequest $request)
    {
        $this->authorize('update', $vehicle);

        $request->has('checkup_date') ? $request['checkup_date'] = Carbon::createFromFormat('d-m-Y', $request->checkup_date)->format('Y-m-d') : null;

        $vehicle->update($request->except('images'));

        if ($request->has('images')) {
            $vehicle->images()->delete();

            foreach ($request->images as $image) {
                $vehicle->images()->create([
                    'path' => uploadImage($image, 'users/vehicles')
                ]);
            }
        }

        return apiResponse( 200 ,
        __('messages.data_updated_successfully', ['attr' => __('messages.vehicle')]) ,
        new VehiclesResource($vehicle)
        );
    }

    public function destroy(UserVehicle $vehicle)
    {
        $this->authorize('delete', $vehicle);

        foreach ($vehicle->images as $image) {
            deleteImage($image->path);
        }

        $vehicle->images()->delete();
        $vehicle->delete();

        return apiResponse( 200 ,
        __('messages.data_deleted_successfully', ['attr' => __('messages.vehicle')]) ,
        null
        );
    }
}
