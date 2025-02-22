<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\StoreMainServiceRequest;
use App\Models\CyPeriodic;
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

}
