<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\SuccessResource;
use App\Http\Resources\API\User\ProviderResources;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderController extends Controller
{
    public function nearbyProviders(Request $request)
    {
        try{
            DB::beginTransaction();
            $providers = Provider::where('is_active' , 1)->nearby($request->latitude, $request->longitude , 10)->map(function($provider){
                return new ProviderResources($provider);
            });

            DB::commit();


            return new SuccessResource([
                    'success'       => true,
                    'data'          => ProviderResources::collection($providers),
                    'message'       => 'Nearby providers retrieved successfully.',
            ]);
        }catch(\Exception $e){
            DB::rollBack();
            dd($e->getMessage());
            return new ErrorResource([
                'success'       => false,
                'message'       => $e->getMessage(),
            ]);
        }
    }
}
