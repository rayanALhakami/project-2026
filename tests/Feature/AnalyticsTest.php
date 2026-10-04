<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Favorite;
use App\Models\Place;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected(): void
    {
        $this->get(route('analytics'))->assertRedirect(route('login'));
        $this->get(route('analytics.data'))->assertRedirect(route('login'));
    }

    public function test_the_page_renders_with_empty_stats(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('analytics'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Analytics')
                ->where('stats.trips_count', 0)
                ->where('stats.upcoming_trips_count', 0)
                ->where('stats.completed_items_count', 0)
                ->where('stats.total_items_count', 0)
                ->where('stats.cities_visited_count', 0)
                ->where('stats.favorite_places_count', 0)
                ->where('stats.top_interests', [])
            );
    }

    public function test_the_page_summarizes_the_users_activity(): void
    {
        $this->travelTo('2026-06-15 10:00:00');

        $user = User::factory()->create();
        $cityA = City::factory()->create();
        $cityB = City::factory()->create();
        $place = Place::factory()->create();

        $trip = Trip::factory()->for($user)->create([
            'city_id' => $cityA->id,
            'city_ids' => [$cityA->id, $cityB->id],
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-17',
            'interests' => ['history'],
        ]);

        $day = TripDay::factory()->for($trip)->create();

        TripItem::factory()->for($day, 'day')->create(['completed_at' => '2026-06-15 09:00:00']);
        TripItem::factory()->for($day, 'day')->create(['completed_at' => null]);

        Favorite::factory()->for($user)->for($place)->create();

        $this->actingAs($user)
            ->get(route('analytics'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Analytics')
                ->where('stats.trips_count', 1)
                ->where('stats.completed_items_count', 1)
                ->where('stats.total_items_count', 2)
                ->where('stats.cities_visited_count', 2)
                ->where('stats.favorite_places_count', 1)
            );
    }

    public function test_upcoming_trips_counts_only_future_or_today_end_dates(): void
    {
        $this->travelTo('2026-06-15 10:00:00');

        $user = User::factory()->create();

        Trip::factory()->for($user)->create(['end_date' => '2026-06-14']);
        Trip::factory()->for($user)->create(['end_date' => '2026-06-16']);

        $this->actingAs($user)
            ->getJson(route('analytics.data'))
            ->assertOk()
            ->assertJsonPath('upcoming_trips_count', 1);
    }

    public function test_top_interests_are_ranked_and_limited(): void
    {
        $user = User::factory()->create();

        Trip::factory()->for($user)->count(3)->create(['interests' => ['history']]);
        Trip::factory()->for($user)->count(2)->create(['interests' => ['food']]);
        Trip::factory()->for($user)->create(['interests' => ['nature']]);
        Trip::factory()->for($user)->create(['interests' => ['shopping']]);
        Trip::factory()->for($user)->create(['interests' => ['adventure']]);
        Trip::factory()->for($user)->create(['interests' => ['culture']]);

        $this->actingAs($user)
            ->getJson(route('analytics.data'))
            ->assertOk()
            ->assertJsonPath('top_interests.0.interest', 'history')
            ->assertJsonPath('top_interests.0.count', 3)
            ->assertJsonCount(5, 'top_interests');
    }

    public function test_the_json_endpoint_matches_the_page_stats(): void
    {
        $this->travelTo('2026-06-15 10:00:00');

        $user = User::factory()->create();
        $cityA = City::factory()->create();
        $cityB = City::factory()->create();
        $place = Place::factory()->create();

        $trip = Trip::factory()->for($user)->create([
            'city_id' => $cityA->id,
            'city_ids' => [$cityA->id, $cityB->id],
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-17',
            'interests' => ['history'],
        ]);

        $day = TripDay::factory()->for($trip)->create();

        TripItem::factory()->for($day, 'day')->create(['completed_at' => '2026-06-15 09:00:00']);
        TripItem::factory()->for($day, 'day')->create(['completed_at' => null]);

        Favorite::factory()->for($user)->for($place)->create();

        $this->actingAs($user)
            ->getJson(route('analytics.data'))
            ->assertOk()
            ->assertJsonPath('trips_count', 1)
            ->assertJsonPath('upcoming_trips_count', 1)
            ->assertJsonPath('completed_items_count', 1)
            ->assertJsonPath('total_items_count', 2)
            ->assertJsonPath('cities_visited_count', 2)
            ->assertJsonPath('favorite_places_count', 1)
            ->assertJsonPath('top_interests.0.interest', 'history')
            ->assertJsonPath('top_interests.0.count', 1);
    }

    public function test_stats_are_isolated_between_users(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $trip = Trip::factory()->for($userA)->create();
        $day = TripDay::factory()->for($trip)->create();
        TripItem::factory()->for($day, 'day')->count(2)->create();
        Favorite::factory()->for($userA)->create();

        $this->actingAs($userB)
            ->getJson(route('analytics.data'))
            ->assertOk()
            ->assertExactJson([
                'trips_count' => 0,
                'upcoming_trips_count' => 0,
                'completed_items_count' => 0,
                'total_items_count' => 0,
                'cities_visited_count' => 0,
                'favorite_places_count' => 0,
                'top_interests' => [],
            ]);
    }
}
