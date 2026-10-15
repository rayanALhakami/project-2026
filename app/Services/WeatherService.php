<?php

namespace App\Services;

use App\Models\City;
use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * @phpstan-type WeatherForecastDay array{date: string, condition: string, conditionEn: string, conditionCode: int, icon: string, high: int, low: int}
 * @phpstan-type WeatherReading array{city: string, cityEn: string, temperature: int, condition: string, conditionEn: string, conditionCode: int, icon: string, high: int, low: int, forecast: list<WeatherForecastDay>}
 */
class WeatherService
{
    /**
     * How long a weather reading may be served from cache, in seconds.
     */
    private const CACHE_SECONDS = 1800;

    /**
     * How long the last successful reading stays available as a fallback, in seconds.
     */
    private const STALE_SECONDS = 86400;

    /**
     * Get the current weather for a single city.
     *
     * @return WeatherReading
     */
    public function forCity(City $city): array
    {
        /** @var WeatherReading $weather */
        $weather = $this->remember(
            "weather:v3:city:{$city->id}",
            fn (): array => $this->fetch([$city])[0] ?? throw new RuntimeException('تعذر جلب بيانات الطقس لمدينة '.$city->name.' حالياً.'),
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
        $weather = $this->remember('weather:v3:all', function (): array {
            $cities = City::query()->orderBy('id')->get();

            if ($cities->isEmpty()) {
                return [];
            }

            return $this->fetch($cities->all());
        });

        return $weather;
    }

    /**
     * Serve a cached reading when fresh, refresh it when expired, and fall back
     * to the last successful reading when every provider fails.
     *
     * @param  Closure(): array<mixed>  $fetch
     * @return array<mixed>
     */
    private function remember(string $key, Closure $fetch): array
    {
        $cached = Cache::get($key);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $fresh = $fetch();

            Cache::put($key, $fresh, self::CACHE_SECONDS);
            Cache::put($key.':stale', $fresh, self::STALE_SECONDS);

            return $fresh;
        } catch (Throwable $exception) {
            $stale = Cache::get($key.':stale');

            if (is_array($stale)) {
                return $stale;
            }

            throw $exception;
        }
    }

    /**
     * Fetch readings from Open-Meteo, falling back to MET Norway when it fails.
     *
     * @param  array<int, City>  $cities
     * @return array<int, WeatherReading>
     */
    private function fetch(array $cities): array
    {
        try {
            return $this->fetchFromOpenMeteo($cities);
        } catch (Throwable $primaryFailure) {
            try {
                return $this->fetchFromMetNo($cities);
            } catch (Throwable) {
                throw $primaryFailure;
            }
        }
    }

    /**
     * Fetch and map live weather readings from Open-Meteo.
     *
     * @param  array<int, City>  $cities
     * @return array<int, WeatherReading>
     */
    private function fetchFromOpenMeteo(array $cities): array
    {
        try {
            $response = Http::timeout(8)
                ->retry(2, 200)
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => implode(',', array_map(fn (City $city): string => (string) $city->latitude, $cities)),
                    'longitude' => implode(',', array_map(fn (City $city): string => (string) $city->longitude, $cities)),
                    'current' => 'temperature_2m,weather_code,wind_speed_10m,relative_humidity_2m',
                    'daily' => 'weather_code,temperature_2m_max,temperature_2m_min',
                    'timezone' => 'Asia/Riyadh',
                    'forecast_days' => 6,
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
     * Fetch and map live weather readings from MET Norway (fallback provider).
     *
     * @param  array<int, City>  $cities
     * @return array<int, WeatherReading>
     */
    private function fetchFromMetNo(array $cities): array
    {
        try {
            $responses = Http::pool(fn (Pool $pool) => collect($cities)->map(
                fn (City $city) => $pool->as((string) $city->id)
                    ->timeout(8)
                    ->withHeaders([
                        'User-Agent' => 'next-trip/1.0 (https://next-trip-production-wbbwxq.laravel.cloud)',
                    ])
                    ->get('https://api.met.no/weatherapi/locationforecast/2.0/compact', [
                        'lat' => round((float) $city->latitude, 4),
                        'lon' => round((float) $city->longitude, 4),
                    ]),
            )->all());
        } catch (Throwable $exception) {
            throw new RuntimeException('تعذر الاتصال بخدمة الطقس، حاول مرة أخرى بعد قليل.', 0, $exception);
        }

        $readings = [];

        foreach ($cities as $city) {
            $response = $responses[(string) $city->id] ?? null;

            if (! $response instanceof Response || ! $response->successful()) {
                throw new RuntimeException('تعذر جلب بيانات الطقس من الخدمة، حاول مرة أخرى بعد قليل.');
            }

            $payload = $response->json();

            if (! is_array($payload)) {
                throw new RuntimeException('بيانات الطقس المستلمة غير صالحة.');
            }

            $readings[] = $this->mapMetNoReading($city, $payload);
        }

        return $readings;
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

        $forecast = $this->forecastDays($daily);

        $temperature = (int) round((float) ($current['temperature_2m'] ?? 0));
        $high = $forecast[0]['high'] ?? (int) round((float) ($daily['temperature_2m_max'][0] ?? $temperature));
        $low = $forecast[0]['low'] ?? (int) round((float) ($daily['temperature_2m_min'][0] ?? $temperature));
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
            'forecast' => $forecast,
        ];
    }

    /**
     * Map a single MET Norway reading onto the frontend weather shape.
     *
     * @param  array<string, mixed>  $payload
     * @return WeatherReading
     */
    private function mapMetNoReading(City $city, array $payload): array
    {
        $series = is_array($payload['properties']['timeseries'] ?? null) ? $payload['properties']['timeseries'] : [];

        if ($series === []) {
            throw new RuntimeException('بيانات الطقس المستلمة غير صالحة.');
        }

        $timezone = new DateTimeZone('Asia/Riyadh');
        $daily = [];

        foreach ($series as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $time = $entry['time'] ?? null;
            $temperature = $entry['data']['instant']['details']['air_temperature'] ?? null;

            if (! is_string($time) || ! is_numeric($temperature)) {
                continue;
            }

            try {
                $date = (new DateTimeImmutable($time))->setTimezone($timezone)->format('Y-m-d');
            } catch (Throwable) {
                continue;
            }

            if (! isset($daily[$date])) {
                $daily[$date] = ['temps' => [], 'symbols' => []];
            }

            $daily[$date]['temps'][] = (float) $temperature;

            $symbol = $entry['data']['next_6_hours']['summary']['symbol_code']
                ?? $entry['data']['next_1_hours']['summary']['symbol_code']
                ?? null;

            if (is_string($symbol) && $symbol !== '') {
                $daily[$date]['symbols'][$symbol] = ($daily[$date]['symbols'][$symbol] ?? 0) + 1;
            }
        }

        ksort($daily);
        $daily = array_slice($daily, 0, 6, true);

        if ($daily === []) {
            throw new RuntimeException('بيانات الطقس المستلمة غير صالحة.');
        }

        $forecast = [];

        foreach ($daily as $date => $day) {
            $high = (int) round(max($day['temps']));
            $low = (int) round(min($day['temps']));
            $symbol = $this->dominantSymbol($day['symbols'] ?? []);

            [$condition, $conditionEn, $icon] = $this->conditionForSymbol($symbol, $high);

            $forecast[] = [
                'date' => (string) $date,
                'condition' => $condition,
                'conditionEn' => $conditionEn,
                'conditionCode' => $this->codeForCondition($condition),
                'icon' => $icon,
                'high' => $high,
                'low' => $low,
            ];
        }

        $first = is_array($series[0]['data'] ?? null) ? $series[0]['data'] : [];
        $temperature = (int) round((float) ($first['instant']['details']['air_temperature'] ?? $forecast[0]['high']));
        $symbol = $first['next_1_hours']['summary']['symbol_code'] ?? null;

        [$condition, $conditionEn, $icon] = $this->conditionForSymbol(is_string($symbol) ? $symbol : null, $temperature);

        return [
            'city' => $city->name,
            'cityEn' => $city->name_en,
            'temperature' => $temperature,
            'condition' => $condition,
            'conditionEn' => $conditionEn,
            'conditionCode' => $this->codeForCondition($condition),
            'icon' => $icon,
            'high' => $forecast[0]['high'],
            'low' => $forecast[0]['low'],
            'forecast' => $forecast,
        ];
    }

    /**
     * Map the daily Open-Meteo series onto six-day forecast entries.
     *
     * @param  array<string, mixed>  $daily
     * @return list<array{date: string, condition: string, conditionEn: string, conditionCode: int, icon: string, high: int, low: int}>
     */
    private function forecastDays(array $daily): array
    {
        $dates = is_array($daily['time'] ?? null) ? $daily['time'] : [];
        $codes = is_array($daily['weather_code'] ?? null) ? $daily['weather_code'] : [];
        $highs = is_array($daily['temperature_2m_max'] ?? null) ? $daily['temperature_2m_max'] : [];
        $lows = is_array($daily['temperature_2m_min'] ?? null) ? $daily['temperature_2m_min'] : [];

        $forecast = [];

        foreach ($dates as $index => $date) {
            $high = (int) round((float) ($highs[$index] ?? 0));
            $low = (int) round((float) ($lows[$index] ?? 0));
            $code = (int) ($codes[$index] ?? 0);

            [$condition, $conditionEn, $icon] = $this->conditionFor($code, $high);

            $forecast[] = [
                'date' => (string) $date,
                'condition' => $condition,
                'conditionEn' => $conditionEn,
                'conditionCode' => $code,
                'icon' => $icon,
                'high' => $high,
                'low' => $low,
            ];
        }

        return $forecast;
    }

    /**
     * Pick the most frequent symbol code for a day.
     *
     * @param  array<string, int>  $symbols
     */
    private function dominantSymbol(array $symbols): ?string
    {
        if ($symbols === []) {
            return null;
        }

        arsort($symbols);

        return (string) array_key_first($symbols);
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

    /**
     * Map a MET Norway symbol code to the frontend condition, label, and icon.
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private function conditionForSymbol(?string $symbol, int $temperature): array
    {
        $symbol = strtolower((string) $symbol);

        if ($symbol === '') {
            return $temperature >= 40
                ? ['hot', 'Hot', '🔥']
                : ['clear', 'Clear', '☀️'];
        }

        return match (true) {
            str_contains($symbol, 'thunder') => ['thunder', 'Thunder', '⛈️'],
            str_contains($symbol, 'rain'),
            str_contains($symbol, 'drizzle'),
            str_contains($symbol, 'sleet'),
            str_contains($symbol, 'snow') => ['rain', 'Rain', '🌧️'],
            str_contains($symbol, 'fog') => ['fog', 'Fog', '🌫️'],
            str_contains($symbol, 'partlycloudy') => ['partly-cloudy', 'Partly cloudy', '⛅'],
            str_contains($symbol, 'cloudy') => ['cloudy', 'Cloudy', '☁️'],
            $temperature >= 40 => ['hot', 'Hot', '🔥'],
            default => ['clear', 'Clear', '☀️'],
        };
    }

    /**
     * Map a frontend condition to an approximate WMO code for payload parity.
     */
    private function codeForCondition(string $condition): int
    {
        return match ($condition) {
            'thunder' => 95,
            'rain' => 61,
            'fog' => 45,
            'partly-cloudy' => 2,
            'cloudy' => 3,
            default => 0,
        };
    }
}
