<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Provider extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function scopeNearby(Builder $query, $latitude, $longitude, $radius = 10)
    {
        $providers = $query->with('district.translations')->get();

        return $providers->filter(function ($provider) use ($latitude, $longitude, $radius) {
            $districtName = $provider->district->getTranslation('name', app()->getLocale());

            $coordinates = $this->getCoordinatesFromDistrictName($districtName);

            if (!$coordinates) {
                return false;
            }

            $distance = $this->calculateDistance($latitude, $longitude, $coordinates['lat'], $coordinates['lng']);

            return $distance < $radius;
        })->map(function ($provider) {
            return [
                'provider'      => $provider,
                'district_name' => $provider->district->getTranslation('name', app()->getLocale()),
                'coordinates'   => $this->getCoordinatesFromDistrictName($provider->district->getTranslation('name', app()->getLocale())),
            ];
        })->values();
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // نصف قطر الأرض بالكيلومترات

        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cys()
    {
        return $this->hasMany(CyPeriodicProvider::class);
    }

    private function getCoordinatesFromDistrictName(mixed $districtName)
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address'   => $districtName->name,
            'key'       => env('GOOGLE_MAPS_API_KEY'),
        ]);

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();

        if ($data['status'] !== 'OK') {
            return null;
        }

        return $data['results'][0]['geometry']['location'];
    }
}
