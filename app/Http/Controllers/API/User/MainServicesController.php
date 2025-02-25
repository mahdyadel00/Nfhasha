<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\StoreMainServiceRequest;
use App\Http\Resources\API\SuccessResource;
use App\Models\CyPeriodic;
use App\Models\ServiceMaintenance;
use App\Models\TypePeriodicInspections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainServicesController extends Controller
{
    public function store(StoreMainServiceRequest $request)
    {
//        try{
//            DB::beginTransaction();
//
//            $mainService = CyPeriodic::create($request->validated());
//        }
    }

    public function index()
    {
        $ervice_maintenance = ServiceMaintenance::paginate(config('app.paginate'));

        return new SuccessResource([
            'data' => $ervice_maintenance
        ]);
    }


    public function show($id)
    {

        $service_maintenance = ServiceMaintenance::find($id);

        return new SuccessResource([
            'data' => $service_maintenance
        ]);
    }

}