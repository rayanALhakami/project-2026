<?php

namespace Tests\Feature;

use App\Enums\PlaceCategory;
use App\Enums\TimeOfDay;
use App\Models\Conversation;
use App\Models\Favorite;
use App\Models\Notification;
use App\Models\Place;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripItem;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourismModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_factory_builds_ordered_days_with_items()
    {
        $trip = Trip::factory()
            ->has(
                TripDay::factory()
                    ->count(3)
                    ->sequence(fn (Sequence $sequence) => ['day_number' => $sequence->index + 1])
                    ->has(TripItem::factory()->count(2), 'items'),
                'days',
            )
            ->create();

        $this->assertCount(3, $trip->days);
        $this->assertSame([1, 2, 3], $trip->days->pluck('day_number')->all());
        $this->assertCount(2, $trip->days->first()->items);
    }

    public function test_place_casts_category_and_best_time_both_ways()
    {
        $place = Place::factory()->create([
            'category' => PlaceCategory::Heritage,
            'best_time' => TimeOfDay::Sunset,
        ]);

        $this->assertDatabaseHas('places', [
            'id' => $place->id,
            'category' => 'heritage',
            'best_time' => 'sunset',
        ]);

        $this->assertSame(PlaceCategory::Heritage, $place->fresh()->category);
        $this->assertSame(TimeOfDay::Sunset, $place->fresh()->best_time);
    }

    public function test_user_exposes_all_tourism_relationships()
    {
        $user = User::factory()
            ->has(Trip::factory()->count(2))
            ->has(Favorite::factory()->for(Place::factory()))
            ->has(Conversation::factory())
            ->has(UserPreference::factory(), 'preference')
            ->has(Notification::factory()->count(3))
            ->create();

        $this->assertCount(2, $user->trips);
        $this->assertCount(1, $user->favorites);
        $this->assertCount(1, $user->conversations);
        $this->assertNotNull($user->preference);
        $this->assertCount(3, $user->notifications);
    }

    public function test_unread_scope_excludes_read_notifications()
    {
        $user = User::factory()->create();

        Notification::factory()->for($user)->count(2)->create();
        Notification::factory()->for($user)->read()->create();

        $this->assertCount(2, Notification::unread()->get());
    }

    public function test_deleting_a_city_cascades_to_its_places()
    {
        $place = Place::factory()->create();

        $place->city->delete();

        $this->assertDatabaseMissing('places', ['id' => $place->id]);
    }
}
