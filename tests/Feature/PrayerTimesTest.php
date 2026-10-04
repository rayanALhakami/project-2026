<?php

namespace Tests\Feature;

use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class PrayerTimesTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_cleaned_prayer_times_and_the_city_id(): void
    {
        $city = City::factory()->create([
            'name' => 'الرياض',
            'name_en' => 'Riyadh',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        $this->travelTo('2026-10-04 09:00:00');

        Http::preventStrayRequests();
        Http::fake([
            'api.aladhan.com/*' => Http::response([
                'code' => 200,
                'status' => 'OK',
                'data' => [
                    'timings' => [
                        'Fajr' => '04:29 (+03)',
                        'Sunrise' => '05:50 (+03)',
                        'Dhuhr' => '11:54 (+03)',
                        'Asr' => '15:17 (+03)',
                        'Maghrib' => '18:00 (+03)',
                        'Isha' => '19:30 (+03)',
                    ],
                ],
            ]),
        ]);

        $response = $this->getJson(route('prayer-times.show', $city));

        $response->assertOk()->assertExactJson([
            'cityId' => $city->id,
            'fajr' => '04:29',
            'dhuhr' => '11:54',
            'asr' => '15:17',
            'maghrib' => '18:00',
            'isha' => '19:30',
        ]);

        Http::assertSent(function (Request $request) use ($city): bool {
            return str_contains($request->url(), 'api.aladhan.com/v1/timings/2026-10-04')
                && (float) $request['latitude'] === (float) $city->latitude
                && (float) $request['longitude'] === (float) $city->longitude
                && $request['method'] === 4;
        });
    }

    public function test_show_serves_a_repeated_call_from_cache_with_one_outbound_request(): void
    {
        $city = City::factory()->create([
            'name' => 'الرياض',
            'name_en' => 'Riyadh',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        $this->travelTo('2026-10-04 09:00:00');

        Http::preventStrayRequests();
        Http::fake([
            'api.aladhan.com/*' => Http::response([
                'data' => [
                    'timings' => [
                        'Fajr' => '04:29 (+03)',
                        'Dhuhr' => '11:54 (+03)',
                        'Asr' => '15:17 (+03)',
                        'Maghrib' => '18:00 (+03)',
                        'Isha' => '19:30 (+03)',
                    ],
                ],
            ]),
        ]);

        $this->getJson(route('prayer-times.show', $city))->assertOk();
        $this->getJson(route('prayer-times.show', $city))->assertOk();

        Http::assertSentCount(1);
    }

    public function test_show_returns_503_with_a_generic_arabic_error_when_aladhan_fails(): void
    {
        $city = City::factory()->create([
            'name' => 'الرياض',
            'name_en' => 'Riyadh',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        $this->travelTo('2026-10-04 09:00:00');

        Sleep::fake();
        Http::preventStrayRequests();
        Http::fake([
            'api.aladhan.com/*' => Http::response('Bad Gateway', 502),
        ]);

        $response = $this->getJson(route('prayer-times.show', $city));

        $response->assertServiceUnavailable();

        $this->assertSame('تعذر جلب البيانات حالياً، حاول مرة أخرى.', $response->json('error'));
        $this->assertStringNotContainsString('Bad Gateway', $response->getContent());
        $this->assertStringNotContainsString('RuntimeException', $response->getContent());
    }
}
