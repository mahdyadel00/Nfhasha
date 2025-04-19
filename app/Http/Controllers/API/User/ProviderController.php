<?php


namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Provider\NearbyProvidersRequest;
use App\Http\Resources\API\Provider\ProviderResource;
use App\Http\Resources\API\SuccessResource;
use App\Http\Resources\API\ErrorResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ProviderController extends Controller
{
    /**
     * Retrieve nearby providers based on the provided coordinates.
     *
     * @param NearbyProvidersRequest $request
     * @return \App\Http\Resources\SuccessResource|\App\Http\Resources\ErrorResource
     */
    public function nearbyProviders(NearbyProvidersRequest $request)
    {
        try {
            $users = User::query()
                ->whereNotNull(['latitude', 'longitude'])
                ->where('role', 'provider')
                ->nearby(
                    latitude: $request->validated('latitude'),
                    longitude: $request->validated('longitude'),
                    distance: $request->validated('distance', 50)
                )
                ->take(10)
                ->get();

            return new SuccessResource([
                'success' => true,
                'data' => ProviderResource::collection($users),
                'message' => __('messages.nearby_providers_retrieved'),
            ]);
        } catch (\Exception $e) {
            Log::channel('error')->error('Error in nearbyProviders: ' . $e->getMessage());
            return new ErrorResource([
                'success' => false,
                'message' => __('messages.error_occurred'),
            ]);
        }
    }

    /**
     * Query scope to filter records by geographical distance using Haversine formula.
     *
     * @param Builder $query
     * @param float $latitude
     * @param float $longitude
     * @param float $distance Distance in kilometers (default: 50)
     * @return Builder
     */
    public function scopeNearby(Builder $query, float $latitude, float $longitude, float $distance = 50): Builder
    {
        // Bounding box to reduce the number of records before applying Haversine
        $latRange = $distance / 110.574; // Approx. 1 degree = 110.574 km
        $lonRange = $distance / (111.320 * cos(deg2rad($latitude))); // Adjust for longitude

        $query->whereBetween('latitude', [$latitude - $latRange, $latitude + $latRange])
            ->whereBetween('longitude', [$longitude - $lonRange, $longitude + $lonRange]);

        $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))";

        return $query
            ->select('*')
            ->selectRaw("{$haversine} AS distance", [$latitude, $longitude, $latitude])
            ->having('distance', '<', $distance)
            ->orderBy('distance');
    }
}
