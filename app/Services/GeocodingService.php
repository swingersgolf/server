<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeocodingService
{
    /**
     * Geocode a postal code to get latitude and longitude using Geocoder.ca API.
     *
     * @param string $postalCode
     * @return array|null
     */
    public function geocodePostalCode(string $postalCode): ?array
    {
    
        // geocoder.ca API endpoint for geocoding
        $response = Http::withoutVerifying()->get('https://geocoder.ca/' . $postalCode . '?json=1');
    
        // Check if the response is successful
        if ($response->successful()) {
            $data = $response->json();
    
            // Ensure that the necessary data exists
            if (isset($data['latt']) && isset($data['longt'])) {
                return [
                    'lat' => $data['latt'],
                    'lon' => $data['longt'],
                ];
            }
        }
    
        // Return null if no valid data is found
        return null;
    }
    
    /**
     * Calculate the distance between two latitude/longitude points using the Haversine formula.
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Radius of Earth in kilometers

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in kilometers
    }
}
