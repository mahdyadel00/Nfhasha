<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\ErrorResource;
use App\Http\Resources\API\SuccessResource;
use App\Http\Resources\API\Provider\ProviderResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProviderController extends Controller
{
    public function nearbyProviders(Request $request)
    {
        try {
            DB::beginTransaction();

            $users = User::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->nearby($request->latitude, $request->longitude, 50)
                ->where('role', 'provider')
                ->orderBy('distance')
                ->take(10)->get();

            DB::commit();

            return new SuccessResource([
                'success'       => true,
                'data'          => ProviderResource::collection($users),
                'message'       => 'Nearby providers retrieved successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('error')->error('Error in nearbyProviders: '.$e->getMessage());
            return new ErrorResource(['success' => false, 'message' => $e->getMessage()]);
        }
    }

}
