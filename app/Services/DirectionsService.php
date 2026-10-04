<?php

namespace App\Services;

use App\Models\City;
use App\Models\Place;
use RuntimeException;

class DirectionsService
{
    /**
     * The assumed average driving speed in km/h used for car time estimates.
     */
    private const CAR_SPEED_KMH = 80.0;

    /**
     * Compute the distance and travel options between two Saudi cities or places.
     *
     * @return array{
     *     from: array{name: string, name_en: string, latitude: float, longitude: float},
     *     to: array{name: string, name_en: string, latitude: float, longitude: float},
     *     distance_km: float,
     *     car_minutes: int,
     *     flight_minutes: int|null,
     *     recommended_mode: 'flight'|'car'
     * }
     */
    public function between(string $from, string $to): array
    {
        $origin = $this->resolve($from);
        $destination = $this->resolve($to);

        $distance = $this->distanceKm(
            $origin['latitude'],
            $origin['longitude'],
            $destination['latitude'],
            $destination['longitude'],
        );

        $carMinutes = $distance <= 0.0 ? 0 : (int) round($distance / self::CAR_SPEED_KMH * 60);
        $flightMinutes = $distance > 350 ? (int) round(60 + $distance / 800 * 60) : null;

        return [
            'from' => $origin,
            'to' => $destination,
            'distance_km' => round($distance, 1),
            'car_minutes' => $carMinutes,
            'flight_minutes' => $flightMinutes,
            'recommended_mode' => $flightMinutes !== null && $flightMinutes < $carMinutes ? 'flight' : 'car',
        ];
    }

    /**
     * Get the great-circle distance between two coordinates in kilometers.
     */
    public function distanceKm(float $fromLat, float $fromLon, float $toLat, float $toLon): float
    {
        $earthRadius = 6371.0;
        $latDelta = deg2rad($toLat - $fromLat);
        $lonDelta = deg2rad($toLon - $fromLon);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($lonDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Resolve a user-provided city or place name to coordinates, preferring cities.
     *
     * @return array{name: string, name_en: string, latitude: float, longitude: float}
     */
    private function resolve(string $name): array
    {
        $needle = trim($name);

        if ($needle === '') {
            throw new RuntimeException('لم أجد اسم المدينة أو المعلم المطلوب. يرجى كتابة اسم واضح مثل «الرياض» أو «جدة».');
        }

        $city = City::query()
            ->where('name', 'like', "%{$needle}%")
            ->orWhere('name_en', 'like', "%{$needle}%")
            ->orderBy('id')
            ->first();

        if ($city instanceof City) {
            return [
                'name' => $city->name,
                'name_en' => $city->name_en,
                'latitude' => (float) $city->latitude,
                'longitude' => (float) $city->longitude,
            ];
        }

        $place = Place::query()
            ->where('name', 'like', "%{$needle}%")
            ->orWhere('name_en', 'like', "%{$needle}%")
            ->orderBy('id')
            ->first();

        if ($place instanceof Place) {
            return [
                'name' => $place->name,
                'name_en' => $place->name_en,
                'latitude' => (float) $place->latitude,
                'longitude' => (float) $place->longitude,
            ];
        }

        throw new RuntimeException('لم أجد «'.$needle.'» في دليل المدن أو المعالم. تحقق من الاسم أو جرّب مدينة قريبة.');
    }
}
