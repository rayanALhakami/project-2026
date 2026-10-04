<?php

namespace Tests\Feature;

use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class WeatherTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_the_mapped_weather_shape_with_a_hot_condition(): void
    {
        $city = City::factory()->create([
            'name' => 'الرياض',
            'name_en' => 'Riyadh',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'api.open-meteo.com/*' => Http::response($this->reading(41.4, 0, 43.2, 28.8)),
        ]);

        $response = $this->getJson(route('weather.show', $city));

        $response->assertOk()->assertExactJson([
            'city' => 'الرياض',
            'cityEn' => 'Riyadh',
            'temperature' => 41,
            'condition' => 'hot',
            'conditionEn' => 'Hot',
            'conditionCode' => 0,
            'icon' => '🔥',
            'high' => 43,
            'low' => 29,
        ]);

        Http::assertSent(function (Request $request): bool {
            return str_contains($request->url(), 'api.open-meteo.com/v1/forecast')
                && $request['latitude'] === '24.7136'
                && $request['longitude'] === '46.6753'
                && $request['current'] === 'temperature_2m,weather_code,wind_speed_10m,relative_humidity_2m'
                && $request['daily'] === 'temperature_2m_max,temperature_2m_min'
                && $request['timezone'] === 'Asia/Riyadh'
                && $request['forecast_days'] === 1;
        });
    }

    public function test_show_maps_a_wmo_weather_code_to_its_condition(): void
    {
        $city = City::factory()->create([
            'name' => 'الرياض',
            'name_en' => 'Riyadh',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'api.open-meteo.com/*' => Http::response($this->reading(24.6, 61, 30.0, 18.0)),
        ]);

        $this->getJson(route('weather.show', $city))
            ->assertOk()
            ->assertJsonPath('condition', 'rain')
            ->assertJsonPath('conditionEn', 'Rain')
            ->assertJsonPath('icon', '🌧️')
            ->assertJsonPath('conditionCode', 61)
            ->assertJsonPath('temperature', 25);
    }

    public function test_show_serves_a_repeated_call_from_cache_with_one_outbound_request(): void
    {
        $city = City::factory()->create([
            'name' => 'الرياض',
            'name_en' => 'Riyadh',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'api.open-meteo.com/*' => Http::response($this->reading(41.4, 0, 43.2, 28.8)),
        ]);

        $this->getJson(route('weather.show', $city))->assertOk();
        $this->getJson(route('weather.show', $city))->assertOk();

        Http::assertSentCount(1);
    }

    public function test_index_maps_every_city_in_id_order(): void
    {
        City::factory()->create([
            'name' => 'جدة',
            'name_en' => 'Jeddah',
            'latitude' => 21.4858,
            'longitude' => 39.1925,
        ]);

        City::factory()->create([
            'name' => 'الرياض',
            'name_en' => 'Riyadh',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                $this->reading(35.2, 1, 38.0, 29.0),
                $this->reading(20.1, 45, 22.0, 12.0),
            ]),
        ]);

        $response = $this->getJson(route('weather.index'));

        $response->assertOk()->assertExactJson([
            [
                'city' => 'جدة',
                'cityEn' => 'Jeddah',
                'temperature' => 35,
                'condition' => 'partly-cloudy',
                'conditionEn' => 'Partly cloudy',
                'conditionCode' => 1,
                'icon' => '⛅',
                'high' => 38,
                'low' => 29,
            ],
            [
                'city' => 'الرياض',
                'cityEn' => 'Riyadh',
                'temperature' => 20,
                'condition' => 'fog',
                'conditionEn' => 'Fog',
                'conditionCode' => 45,
                'icon' => '🌫️',
                'high' => 22,
                'low' => 12,
            ],
        ]);

        Http::assertSent(function (Request $request): bool {
            return $request['latitude'] === '21.4858,24.7136'
                && $request['longitude'] === '39.1925,46.6753';
        });
    }

    public function test_show_returns_503_with_a_generic_arabic_error_when_open_meteo_fails(): void
    {
        $city = City::factory()->create([
            'name' => 'الرياض',
            'name_en' => 'Riyadh',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        Sleep::fake();
        Http::preventStrayRequests();
        Http::fake([
            'api.open-meteo.com/*' => Http::response('Service Unavailable', 500),
        ]);

        $response = $this->getJson(route('weather.show', $city));

        $response->assertServiceUnavailable();

        $this->assertSame('تعذر جلب البيانات حالياً، حاول مرة أخرى.', $response->json('error'));
        $this->assertStringNotContainsString('Service Unavailable', $response->getContent());
        $this->assertStringNotContainsString('RuntimeException', $response->getContent());
    }

    public function test_index_returns_503_with_a_generic_arabic_error_when_the_connection_fails(): void
    {
        City::factory()->create([
            'name' => 'الرياض',
            'name_en' => 'Riyadh',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        Sleep::fake();
        Http::preventStrayRequests();
        Http::fake([
            'api.open-meteo.com/*' => Http::failedConnection('Open-Meteo is unreachable.'),
        ]);

        $response = $this->getJson(route('weather.index'));

        $response->assertServiceUnavailable();

        $this->assertSame('تعذر جلب البيانات حالياً، حاول مرة أخرى.', $response->json('error'));
        $this->assertStringNotContainsString('Open-Meteo is unreachable', $response->getContent());
        $this->assertMatchesRegularExpression('/\p{Arabic}/u', $response->json('error'));
    }

    private function reading(float $temperature, int $code, float $high, float $low): array
    {
        return [
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'current' => [
                'temperature_2m' => $temperature,
                'weather_code' => $code,
                'wind_speed_10m' => 11.5,
                'relative_humidity_2m' => 12,
            ],
            'daily' => [
                'temperature_2m_max' => [$high],
                'temperature_2m_min' => [$low],
            ],
        ];
    }
}
