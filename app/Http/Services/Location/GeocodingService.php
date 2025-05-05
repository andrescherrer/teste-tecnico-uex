<?php

namespace App\Http\Services\Location;

use Illuminate\Support\Facades\Http;

class GeocodingService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://maps.googleapis.com/maps/api/geocode/json';

    public function __construct()
    {
        $this->apiKey = env('GOOGLE_MAPS_API_KEY');
    }

    public function execute(string $address): ?array
    {
        $response = Http::get($this->baseUrl, [
            'address' => $address,
            'key'     => $this->apiKey
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data['results'][0]['geometry']['location'])) {
                $location = $data['results'][0]['geometry']['location'];
                return [
                    'lat' => $location['lat'],
                    'lng' => $location['lng'],
                ];
            }
        }

        return null;
    }
}
