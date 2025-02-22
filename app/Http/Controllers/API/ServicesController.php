<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ServicesResource;
use App\Models\Service;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $services = Service::active()->get();

        return apiResponse(200,
        translate('messages.services_successfully_retrieved') ,
         ServicesResource::collection($services));
    }

    
}
