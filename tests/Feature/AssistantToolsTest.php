<?php

namespace Tests\Feature;

use App\Ai\Tools\BuildItinerary;
use App\Ai\Tools\ComparePlaces;
use App\Ai\Tools\EstimateBudget;
use App\Ai\Tools\FindBestFor;
use App\Ai\Tools\GetDirections;
use App\Ai\Tools\GetWeather;
use App\Ai\Tools\ListEvents;
use App\Ai\Tools\RecommendPlaces;
use App\Ai\Tools\SearchPlaces;
use App\Models\Event;
use App\Models\Place;
use Database\Seeders\CitySeeder;
use Database\Seeders\PlaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Tools\Request as ToolRequest;
use Tests\TestCase;

class AssistantToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_places_finds_hegra_in_alula(): void
    {
        $this->seed();

        $result = json_decode((string) (new SearchPlaces)->handle(new ToolRequest([
            'query' => 'Hegra',
        ])), true);

        $this->assertNotEmpty($result);
        $this->assertSame('Hegra', $result[0]['name_en']);
        $this->assertSame('العلا', $result[0]['city']);
    }

    public function test_search_places_ignores_an_invalid_category_without_results(): void
    {
        $this->seed();

        $result = (string) (new SearchPlaces)->handle(new ToolRequest([
            'query' => 'مكان غير موجود إطلاقاً في الدليل',
            'category' => 'not-a-real-category',
        ]));

        $this->assertSame([], json_decode($result, true));
    }

    public function test_search_places_respects_the_limit(): void
    {
        $this->seed();

        $result = json_decode((string) (new SearchPlaces)->handle(new ToolRequest([
            'limit' => 3,
        ])), true);

        $this->assertCount(3, $result);
    }

    public function test_compare_places_returns_requested_places_in_order_and_an_error_for_missing_ids(): void
    {
        $this->seed();

        $ids = Place::query()->orderBy('id')->take(2)->pluck('id')->all();

        $result = json_decode((string) (new ComparePlaces)->handle(new ToolRequest([
            'place_ids' => [$ids[0], $ids[1], 99999],
        ])), true);

        $this->assertCount(3, $result);
        $this->assertSame($ids[0], $result[0]['id']);
        $this->assertSame($ids[1], $result[1]['id']);
        $this->assertArrayHasKey('error', $result[2]);
        $this->assertSame(99999, $result[2]['id']);
    }

    public function test_estimate_budget_totals_the_breakdown_in_sar(): void
    {
        $this->seed();

        $result = json_decode((string) (new EstimateBudget)->handle(new ToolRequest([
            'city' => 'العلا',
            'days' => 3,
            'travelers' => 2,
            'style' => 'comfort',
        ])), true);

        $expectedTotal = round(
            $result['tickets'] + $result['meals'] + $result['transport'] + $result['hotel'],
            2,
        );

        $this->assertIsNumeric($result['total']);
        $this->assertEqualsWithDelta($expectedTotal, $result['total'], 0.01);
        $this->assertSame('SAR', $result['currency']);
    }

    public function test_build_itinerary_returns_the_requested_days_with_seeded_places(): void
    {
        $this->seed();

        $result = json_decode((string) (new BuildItinerary)->handle(new ToolRequest([
            'city' => 'الرياض',
            'days' => 2,
            'travelers' => 2,
        ])), true);

        $this->assertCount(2, $result['days']);
        $this->assertIsNumeric($result['estimated_ticket_total']);

        foreach ($result['days'] as $day) {
            $this->assertNotEmpty($day['items']);
            $this->assertLessThanOrEqual(5, count($day['items']));

            $activities = array_filter(
                $day['items'],
                fn (array $item): bool => $item['category'] !== 'restaurant',
            );

            $this->assertGreaterThanOrEqual(1, count($activities));
            $this->assertLessThanOrEqual(4, count($activities));

            foreach ($day['items'] as $item) {
                $this->assertTrue(
                    Place::query()->whereKey($item['place_id'])->exists(),
                    "Itinerary place [{$item['place_id']}] does not exist.",
                );
            }
        }
    }

    public function test_build_itinerary_reuses_places_when_days_exceed_the_city_places(): void
    {
        $this->seed();

        $result = json_decode((string) (new BuildItinerary)->handle(new ToolRequest([
            'city' => 'العلا',
            'days' => 5,
            'travelers' => 2,
        ])), true);

        $this->assertCount(5, $result['days']);

        $scheduledPlaceIds = [];

        foreach ($result['days'] as $day) {
            $this->assertNotEmpty($day['items']);

            foreach ($day['items'] as $item) {
                $this->assertTrue(
                    Place::query()->whereKey($item['place_id'])->exists(),
                    "Itinerary place [{$item['place_id']}] does not exist.",
                );

                $scheduledPlaceIds[] = $item['place_id'];
            }
        }

        $this->assertGreaterThan(
            count(array_unique($scheduledPlaceIds)),
            count($scheduledPlaceIds),
            'Expected at least one place to be reused across the itinerary days.',
        );
    }

    public function test_list_events_reports_no_upcoming_events_in_arabic(): void
    {
        $this->seed([CitySeeder::class, PlaceSeeder::class]);

        $result = (string) (new ListEvents)->handle(new ToolRequest([]));

        $this->assertNotSame('', $result);
        $this->assertMatchesRegularExpression('/\p{Arabic}/u', $result);
        $this->assertStringContainsString('لا توجد فعاليات', $result);
    }

    public function test_list_events_returns_the_earliest_seeded_event_with_its_city_and_dates(): void
    {
        $this->seed();

        $event = Event::query()->with('city')->orderBy('start_date')->firstOrFail();

        $result = (string) (new ListEvents)->handle(new ToolRequest([]));
        $payload = json_decode($result, true);

        $this->assertCount(8, $payload);
        $this->assertSame($event->name, $payload[0]['name']);
        $this->assertSame($event->city->name, $payload[0]['city']);
        $this->assertSame($event->start_date->toDateString(), $payload[0]['start_date']);
        $this->assertSame($event->end_date->toDateString(), $payload[0]['end_date']);

        $this->assertStringContainsString($event->name, $result);
        $this->assertStringContainsString($event->city->name, $result);
    }

    public function test_recommend_places_returns_seeded_places_for_a_city(): void
    {
        $this->seed();

        $result = json_decode((string) (new RecommendPlaces)->handle(new ToolRequest([
            'city' => 'العلا',
            'interests' => [],
            'limit' => 3,
        ])), true);

        $this->assertNotEmpty($result);

        foreach ($result as $place) {
            $this->assertTrue(Place::query()->whereKey($place['id'])->exists());
        }
    }

    public function test_find_best_for_returns_seeded_places_for_a_need(): void
    {
        $this->seed();

        $result = json_decode((string) (new FindBestFor)->handle(new ToolRequest([
            'need' => 'قهوة مختصة',
            'city' => 'العلا',
        ])), true);

        $this->assertNotEmpty($result);

        foreach ($result as $place) {
            $this->assertTrue(Place::query()->whereKey($place['id'])->exists());
        }
    }

    public function test_get_directions_resolves_riyadh_to_jeddah_with_a_flight_recommendation(): void
    {
        $this->seed();

        $result = json_decode((string) (new GetDirections)->handle(new ToolRequest([
            'from' => 'الرياض',
            'to' => 'جدة',
        ])), true);

        $this->assertSame('الرياض', $result['from']['name']);
        $this->assertSame('Riyadh', $result['from']['name_en']);
        $this->assertSame('جدة', $result['to']['name']);
        $this->assertSame('Jeddah', $result['to']['name_en']);
        $this->assertEqualsWithDelta(845.0, $result['distance_km'], 15.0);
        $this->assertSame('flight', $result['recommended_mode']);
        $this->assertIsInt($result['car_minutes']);
        $this->assertIsInt($result['flight_minutes']);
    }

    public function test_get_directions_returns_an_arabic_not_found_message_for_an_unknown_destination(): void
    {
        $this->seed();

        $result = (string) (new GetDirections)->handle(new ToolRequest([
            'from' => 'الرياض',
            'to' => 'مدينة غير موجودة إطلاقاً',
        ]));

        $this->assertMatchesRegularExpression('/\p{Arabic}/u', $result);
        $this->assertStringContainsString('لم أجد', $result);
    }

    public function test_get_weather_returns_the_resolved_city_and_temperature_from_live_data(): void
    {
        $this->seed();

        Http::preventStrayRequests();
        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 22.4,
                    'weather_code' => 3,
                    'wind_speed_10m' => 8.0,
                    'relative_humidity_2m' => 30,
                ],
                'daily' => [
                    'temperature_2m_max' => [26.0],
                    'temperature_2m_min' => [14.0],
                ],
            ]),
        ]);

        $result = (string) (new GetWeather)->handle(new ToolRequest(['city' => 'أبها']));
        $payload = json_decode($result, true);

        $this->assertSame('أبها', $payload['city']);
        $this->assertSame('Abha', $payload['cityEn']);
        $this->assertSame(22, $payload['temperature']);
        $this->assertStringContainsString('أبها', $result);
        $this->assertStringContainsString('22', $result);

        Http::assertSentCount(1);
    }

    public function test_get_weather_returns_an_arabic_error_for_an_unknown_city(): void
    {
        $this->seed();

        Http::preventStrayRequests();

        $result = (string) (new GetWeather)->handle(new ToolRequest([
            'city' => 'مدينة غير موجودة إطلاقاً',
        ]));

        $this->assertMatchesRegularExpression('/\p{Arabic}/u', $result);
        $this->assertStringContainsString('لم أجد مدينة', $result);
    }
}
