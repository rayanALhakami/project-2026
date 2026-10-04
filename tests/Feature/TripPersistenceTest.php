<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Place;
use App\Models\Trip;
use App\Models\TripItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TripPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_store_trips(): void
    {
        $this->post(route('trips.store'), [])->assertRedirect(route('login'));
    }

    public function test_users_can_save_a_generated_trip(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        $place = Place::factory()->create(['city_id' => $city->id]);

        $response = $this->actingAs($user)->post(route('trips.store'), $this->payload($city->id, $place->id));

        $response->assertRedirect();

        $trip = Trip::with('days.items')->firstOrFail();

        $this->assertSame($user->id, $trip->user_id);
        $this->assertSame($city->id, $trip->city_id);
        $this->assertSame([$city->id], $trip->city_ids);
        $this->assertSame(3, $trip->travelers_count);
        $this->assertSame('2026-11-01', $trip->start_date->toDateString());
        $this->assertSame('2026-11-02', $trip->end_date->toDateString());
        $this->assertStringContainsString($city->name, $trip->title);
        $this->assertCount(2, $trip->days);
        $this->assertSame(1, $trip->days->first()->items->count());
        $this->assertSame($place->id, $trip->days->first()->items->first()->place_id);
        $this->assertSame($city->id, $trip->days->first()->city_id);
        $this->assertSame($city->id, $trip->days->last()->city_id);

        $this->assertDatabaseHas('trip_items', [
            'place_id' => $place->id,
            'start_time' => '09:00',
            'duration_minutes' => 90,
        ]);
    }

    public function test_days_can_belong_to_different_cities(): void
    {
        $user = User::factory()->create();
        $cityA = City::factory()->create();
        $cityB = City::factory()->create();
        $placeA = Place::factory()->create(['city_id' => $cityA->id]);
        $placeB = Place::factory()->create(['city_id' => $cityB->id]);

        $response = $this->actingAs($user)->post(route('trips.store'), [
            'city_ids' => [$cityA->id, $cityB->id],
            'start_date' => '2026-11-01',
            'days' => 2,
            'travelers' => 2,
            'plan' => [
                [
                    'day' => 1,
                    'date' => '2026-11-01',
                    'city_id' => $cityA->id,
                    'entries' => [
                        ['place_id' => $placeA->id, 'time' => '09:00', 'duration' => 60],
                    ],
                ],
                [
                    'day' => 2,
                    'date' => '2026-11-02',
                    'city_id' => $cityB->id,
                    'entries' => [
                        ['place_id' => $placeB->id, 'time' => '10:00', 'duration' => 60],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();

        $trip = Trip::with('days')->firstOrFail();

        $this->assertSame($cityA->id, $trip->days->first()->city_id);
        $this->assertSame($cityB->id, $trip->days->last()->city_id);
    }

    public function test_planner_and_dashboard_restore_the_latest_trip(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        $place = Place::factory()->create(['city_id' => $city->id]);

        $this->actingAs($user)->post(route('trips.store'), $this->payload($city->id, $place->id));

        $trip = Trip::firstOrFail();

        $this->actingAs($user)->get(route('trips'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('TripPlanner')
                ->where('savedTrip.id', $trip->id)
                ->where('savedTrip.days_count', 2)
                ->where('savedTrip.days.0.items.0.place_id', $place->id)
                ->where('savedTrip.days.0.city_id', $city->id)
            );

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('savedTrip.id', $trip->id)
                ->where('savedTrip.days_count', 2)
                ->where('savedTrip.travelers_count', 3)
            );
    }

    public function test_users_can_toggle_item_completion(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        $place = Place::factory()->create(['city_id' => $city->id]);

        $this->actingAs($user)->post(route('trips.store'), $this->payload($city->id, $place->id));

        $item = TripItem::firstOrFail();

        $this->actingAs($user)
            ->patch(route('trip-items.toggle', $item))
            ->assertOk()
            ->assertJson(['completed' => true]);

        $this->assertNotNull($item->fresh()->completed_at);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('savedTrip.completed_count', 1)
                ->where('savedTrip.items_count', 1)
            );

        $this->actingAs($user)
            ->patch(route('trip-items.toggle', $item))
            ->assertOk()
            ->assertJson(['completed' => false]);

        $this->assertNull($item->fresh()->completed_at);
    }

    public function test_store_rejects_an_empty_payload_without_persisting_anything(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('trips.store'), []);

        $response->assertSessionHasErrors(['city_ids', 'start_date', 'days', 'travelers', 'plan']);
        $this->assertDatabaseCount('trips', 0);
        $this->assertDatabaseCount('trip_days', 0);
        $this->assertDatabaseCount('trip_items', 0);
    }

    public function test_store_rejects_invalid_city_plan_and_range_values(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('trips.store'), [
            'city_ids' => [99999],
            'start_date' => '2026-11-01',
            'days' => 15,
            'travelers' => 0,
            'plan' => [
                [
                    'day' => 1,
                    'date' => '2026-11-01',
                    'city_id' => 99999,
                    'entries' => [
                        ['place_id' => 99999, 'time' => '9am', 'duration' => -5],
                    ],
                ],
            ],
        ]);

        $response->assertSessionHasErrors([
            'city_ids.0',
            'days',
            'travelers',
            'plan.0.city_id',
            'plan.0.entries.0.place_id',
            'plan.0.entries.0.time',
            'plan.0.entries.0.duration',
        ]);
        $this->assertDatabaseCount('trips', 0);
        $this->assertDatabaseCount('trip_days', 0);
        $this->assertDatabaseCount('trip_items', 0);
    }

    public function test_planner_and_dashboard_do_not_expose_another_users_trip(): void
    {
        $owner = User::factory()->create();
        $city = City::factory()->create();
        $place = Place::factory()->create(['city_id' => $city->id]);

        $this->actingAs($owner)->post(route('trips.store'), $this->payload($city->id, $place->id));

        $this->assertDatabaseCount('trips', 1);

        $other = User::factory()->create();

        $this->actingAs($other)->get(route('trips'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('TripPlanner')
                ->where('savedTrip', null)
            );

        $this->actingAs($other)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('savedTrip', null)
            );
    }

    public function test_users_cannot_toggle_other_users_items(): void
    {
        $owner = User::factory()->create();
        $city = City::factory()->create();
        $place = Place::factory()->create(['city_id' => $city->id]);

        $this->actingAs($owner)->post(route('trips.store'), $this->payload($city->id, $place->id));

        $item = TripItem::firstOrFail();

        $this->actingAs(User::factory()->create())
            ->patch(route('trip-items.toggle', $item))
            ->assertForbidden();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(int $cityId, int $placeId): array
    {
        return [
            'city_ids' => [$cityId],
            'start_date' => '2026-11-01',
            'days' => 2,
            'travelers' => 3,
            'budget' => 2500,
            'interests' => ['heritage'],
            'plan' => [
                [
                    'day' => 1,
                    'date' => '2026-11-01',
                    'city_id' => $cityId,
                    'entries' => [
                        ['place_id' => $placeId, 'time' => '09:00', 'duration' => 90],
                    ],
                ],
                ['day' => 2, 'date' => '2026-11-02', 'city_id' => $cityId, 'entries' => []],
            ],
        ];
    }
}
