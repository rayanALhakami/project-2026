<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * @phpstan-type WeatherReading array{city: string, cityEn: string, temperature: int, condition: string, conditionEn: string, conditionCode: int, icon: string, high: int, low: int}
 */
class WeatherService
{
    /**
     * How long a weather reading may be served from cache, in seconds.
     */
    private const CACHE_SECONDS = 900;

    /**
     * Get the current weather for a single city.
     *
     * @return WeatherReading
     */
    public function forCity(City $city): array
    {
        /** @var WeatherReading $weather */
        $weather = Cache::remember(
            "weather:city:{$city->id}",
            self::CACHE_SECONDS,
            function () use ($city): array {
                $readings = $this->fetch([$city]);

                return $readings[0] ?? throw new RuntimeException('تعذر جلب بيانات الطقس لمدينة '.$city->name.' حالياً.');
            },
        );

        return $weather;
    }

    /**
     * Get the current weather for every city, ordered by city id.
     *
     * @return array<int, WeatherReading>
     */
    public function forAllCities(): array
    {
        /** @var array<int, WeatherReading> $weather */
        $weather = Cache::remember('weather:all', self::CACHE_SECONDS, function (): array {
            $cities = City::query()->orderBy('id')->get();

            if ($cities->isEmpty()) {
                return [];
            }

            return $this->fetch($cities->all());
        });

        return $weather;
    }

    /**
     * Fetch and map live weather readings for the given cities.
     *
     * @param  array<int, City>  $cities
     * @return array<int, WeatherReading>
     */
    private function fetch(array $cities): array
    {
        try {
            $response = Http::timeout(8)
                ->retry(2, 200)
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => implode(',', array_map(fn (City $city): string => (string) $city->latitude, $cities)),
                    'longitude' => implode(',', array_map(fn (City $city): string => (string) $city->longitude, $cities)),
                    'current' => 'temperature_2m,weather_code,wind_speed_10m,relative_humidity_2m',
                    'daily' => 'temperature_2m_max,temperature_2m_min',
                    'timezone' => 'Asia/Riyadh',
                    'forecast_days' => 1,
                ]);
        } catch (Throwable $exception) {
            throw new RuntimeException('تعذر الاتصال بخدمة الطقس، حاول مرة أخرى بعد قليل.', 0, $exception);
        }

        if (! $response->successful()) {
            throw new RuntimeException('تعذر جلب بيانات الطقس من الخدمة، حاول مرة أخرى بعد قليل.');
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('بيانات الطقس المستلمة غير صالحة.');
        }

        $readings = array_is_list($payload) ? $payload : [$payload];

        return array_map(
            fn (City $city, mixed $reading): array => $this->mapReading($city, $reading),
            $cities,
            array_slice($readings, 0, count($cities)),
        );
    }

    /**
     * Map a single Open-Meteo reading onto the frontend weather shape.
     *
     * @return WeatherReading
     */
    private function mapReading(City $city, mixed $reading): array
    {
        $current = is_array($reading) && is_array($reading['current'] ?? null) ? $reading['current'] : [];
        $daily = is_array($reading) && is_array($reading['daily'] ?? null) ? $reading['daily'] : [];

        $temperature = (int) round((float) ($current['temperature_2m'] ?? 0));
        $high = (int) round((float) ($daily['temperature_2m_max'][0] ?? $temperature));
        $low = (int) round((float) ($daily['temperature_2m_min'][0] ?? $temperature));
        $code = (int) ($current['weather_code'] ?? 0);

        [$condition, $conditionEn, $icon] = $this->conditionFor($code, $temperature);

        return [
            'city' => $city->name,
            'cityEn' => $city->name_en,
            'temperature' => $temperature,
            'condition' => $condition,
            'conditionEn' => $conditionEn,
            'conditionCode' => $code,
            'icon' => $icon,
            'high' => $high,
            'low' => $low,
        ];
    }

    /**
     * Map a WMO weather code to the frontend condition, English label, and icon.
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private function conditionFor(int $code, int $temperature): array
    {
        if ($code === 0 && $temperature >= 40) {
            return ['hot', 'Hot', '🔥'];
        }

        return match (true) {
            $code === 0 => ['clear', 'Clear', '☀️'],
            in_array($code, [1, 2], true) => ['partly-cloudy', 'Partly cloudy', '⛅'],
            $code === 3 => ['cloudy', 'Cloudy', '☁️'],
            in_array($code, [45, 48], true) => ['fog', 'Fog', '🌫️'],
            in_array($code, [51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 71, 73, 75, 77, 80, 81, 82, 85, 86], true) => ['rain', 'Rain', '🌧️'],
            in_array($code, [95, 96, 99], true) => ['thunder', 'Thunder', '⛈️'],
            default => ['clear', 'Clear', '☀️'],
        };
    }
}
