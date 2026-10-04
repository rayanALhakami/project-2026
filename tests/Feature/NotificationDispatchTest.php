<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Event;
use App\Models\Notification;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class NotificationDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_one_trip_reminder_for_a_trip_starting_in_two_days_and_does_not_duplicate_it(): void
    {
        $this->travelTo('2026-01-15 09:00:00');

        $city = City::factory()->create(['name' => 'الرياض', 'name_en' => 'Riyadh']);
        $user = User::factory()->create();
        $trip = Trip::factory()->for($user)->for($city)->create([
            'title' => 'رحلة الرياض',
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
        ]);

        Http::preventStrayRequests();
        Http::fake(['api.open-meteo.com/*' => Http::response($this->reading(24))]);

        $this->artisan('notifications:dispatch')->assertSuccessful();

        $notification = Notification::query()->where('type', 'trip_reminder')->sole();

        $this->assertSame($user->id, $notification->user_id);
        $this->assertSame($trip->id, $notification->data['trip_id']);
        $this->assertNull($notification->read_at);
        $this->assertDatabaseCount('notifications', 1);

        $this->artisan('notifications:dispatch')->assertSuccessful();

        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_creates_one_weather_alert_for_a_hot_city_and_does_not_duplicate_it(): void
    {
        $this->travelTo('2026-01-15 09:00:00');

        $city = City::factory()->create([
            'name' => 'جدة',
            'name_en' => 'Jeddah',
            'latitude' => 21.4858,
            'longitude' => 39.1925,
        ]);
        $user = User::factory()->create();
        Trip::factory()->for($user)->for($city)->create([
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
        ]);

        Http::preventStrayRequests();
        Http::fake(['api.open-meteo.com/*' => Http::response($this->reading(44))]);

        $this->artisan('notifications:dispatch')->assertSuccessful();

        $notification = Notification::query()->where('type', 'weather_alert')->sole();

        $this->assertSame($user->id, $notification->user_id);
        $this->assertSame($city->id, $notification->data['city_id']);
        $this->assertSame(1, Notification::query()->where('type', 'trip_reminder')->count());

        $this->artisan('notifications:dispatch')->assertSuccessful();

        $this->assertSame(1, Notification::query()->where('type', 'weather_alert')->count());
    }

    public function test_creates_one_nearby_event_notification_and_does_not_duplicate_it(): void
    {
        $this->travelTo('2026-01-15 09:00:00');

        $city = City::factory()->create(['name' => 'الرياض', 'name_en' => 'Riyadh']);
        $user = User::factory()->create();
        Trip::factory()->for($user)->for($city)->create([
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
        ]);
        $event = Event::factory()->for($city)->create([
            'name' => 'موسم الرياض',
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]);

        Http::preventStrayRequests();

        $this->artisan('notifications:dispatch')->assertSuccessful();

        $notification = Notification::query()->where('type', 'nearby_event')->sole();

        $this->assertSame($user->id, $notification->user_id);
        $this->assertSame($event->id, $notification->data['event_id']);
        $this->assertDatabaseCount('notifications', 1);

        $this->artisan('notifications:dispatch')->assertSuccessful();

        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_creates_the_trip_reminder_when_the_weather_service_fails(): void
    {
        $this->travelTo('2026-01-15 09:00:00');

        $city = City::factory()->create([
            'name' => 'الرياض',
            'name_en' => 'Riyadh',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);
        $user = User::factory()->create();
        $trip = Trip::factory()->for($user)->for($city)->create([
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
        ]);

        Sleep::fake();
        Http::preventStrayRequests();
        Http::fake(['api.open-meteo.com/*' => Http::response('Service Unavailable', 500)]);

        $this->artisan('notifications:dispatch')->assertSuccessful();

        $notification = Notification::query()->where('type', 'trip_reminder')->sole();

        $this->assertSame($user->id, $notification->user_id);
        $this->assertSame($trip->id, $notification->data['trip_id']);
        $this->assertSame(0, Notification::query()->where('type', 'weather_alert')->count());
    }

    public function test_creates_one_weather_alert_per_city_in_a_multi_city_trip_and_does_not_duplicate_them(): void
    {
        $this->travelTo('2026-01-15 09:00:00');

        $firstCity = City::factory()->create([
            'name' => 'الرياض',
            'name_en' => 'Riyadh',
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);
        $secondCity = City::factory()->create([
            'name' => 'الدمام',
            'name_en' => 'Dammam',
            'latitude' => 26.4207,
            'longitude' => 50.0888,
        ]);
        $user = User::factory()->create();
        Trip::factory()->for($user)->for($firstCity)->create([
            'city_ids' => [$firstCity->id, $secondCity->id],
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
        ]);

        Http::preventStrayRequests();
        Http::fake(['api.open-meteo.com/*' => Http::response($this->reading(44))]);

        $this->artisan('notifications:dispatch')->assertSuccessful();

        $alerts = Notification::query()->where('type', 'weather_alert')->get();

        $this->assertCount(2, $alerts);
        $this->assertSame(
            [$firstCity->id, $secondCity->id],
            $alerts->map(fn (Notification $notification): int => $notification->data['city_id'])->sort()->values()->all(),
        );
        $this->assertSame([$user->id], $alerts->pluck('user_id')->unique()->values()->all());

        $this->artisan('notifications:dispatch')->assertSuccessful();

        $this->assertSame(2, Notification::query()->where('type', 'weather_alert')->count());
    }

    public function test_creates_the_trip_reminder_from_the_riyadh_calendar_when_utc_date_differs(): void
    {
        $this->travelTo('2026-01-15 21:30:00');

        $city = City::factory()->create(['name' => 'الرياض', 'name_en' => 'Riyadh']);
        $user = User::factory()->create();
        $trip = Trip::factory()->for($user)->for($city)->create([
            'start_date' => '2026-01-18',
            'end_date' => '2026-01-20',
        ]);

        Http::preventStrayRequests();
        Http::fake(['api.open-meteo.com/*' => Http::response($this->reading(24))]);

        $this->artisan('notifications:dispatch')->assertSuccessful();

        $notification = Notification::query()->where('type', 'trip_reminder')->sole();

        $this->assertSame($user->id, $notification->user_id);
        $this->assertSame($trip->id, $notification->data['trip_id']);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_does_not_create_a_nearby_event_notification_for_a_trip_that_has_ended(): void
    {
        $this->travelTo('2026-01-15 09:00:00');

        $city = City::factory()->create(['name' => 'الرياض', 'name_en' => 'Riyadh']);
        $user = User::factory()->create();
        Trip::factory()->for($user)->for($city)->create([
            'start_date' => '2026-01-05',
            'end_date' => '2026-01-08',
        ]);
        Event::factory()->for($city)->create([
            'name' => 'موسم الرياض',
            'start_date' => '2026-01-18',
            'end_date' => '2026-01-20',
        ]);

        Http::preventStrayRequests();

        $this->artisan('notifications:dispatch')->assertSuccessful();

        $this->assertSame(0, Notification::query()->where('type', 'nearby_event')->count());
        $this->assertDatabaseCount('notifications', 0);
    }

    /**
     * Build a fake Open-Meteo reading for a single city.
     *
     * @return array<string, mixed>
     */
    private function reading(float $temperature, int $code = 0): array
    {
        return [
            'current' => [
                'temperature_2m' => $temperature,
                'weather_code' => $code,
                'wind_speed_10m' => 11.5,
                'relative_humidity_2m' => 12,
            ],
            'daily' => [
                'temperature_2m_max' => [$temperature + 2],
                'temperature_2m_min' => [$temperature - 6],
            ],
        ];
    }
}
