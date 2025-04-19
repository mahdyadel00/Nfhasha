<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProviderService
{
    public function findNearbyProviders(float $latitude, float $longitude, string $serviceType, float $distance = 50, int $limit = 10): Collection
    {
        return User::query()
            ->whereNotNull(['latitude', 'longitude'])
            ->where('role', 'provider')
            ->whereHas('provider', fn($query) => $query->where($serviceType, true)->where('is_active', true))
            ->nearby($latitude, $longitude, $distance)
            ->take($limit)
            ->get();
    }
}
