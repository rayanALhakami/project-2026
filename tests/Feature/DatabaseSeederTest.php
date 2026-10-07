<?php

namespace Tests\Feature;

use App\Enums\PlaceCategory;
use App\Models\City;
use App\Models\Event;
use App\Models\Place;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_the_saudi_catalogue()
    {
        $this->seed();

        $this->assertSame(9, City::query()->count());
        $this->assertSame(28, Place::query()->count());

        $hegra = Place::query()->where('name_en', 'Hegra')->firstOrFail();

        $this->assertSame('العلا', $hegra->city->name);
        $this->assertSame(PlaceCategory::Heritage, $hegra->category);
        $this->assertSame('95.00', $hegra->ticket_price);
        $this->assertSame('https://www.experiencealula.com', $hegra->booking_url);
        $this->assertFalse($hegra->wheelchair_accessible);
        $this->assertTrue($hegra->prayer_facilities);
    }

    public function test_seeding_twice_does_not_duplicate_records()
    {
        $this->seed();
        $this->seed();

        $this->assertSame(9, City::query()->count());
        $this->assertSame(28, Place::query()->count());
    }

    public function test_every_place_belongs_to_a_city_and_has_a_valid_category()
    {
        $this->seed();

        $places = Place::query()->with('city')->get();

        $this->assertTrue($places->every(fn (Place $place): bool => $place->city !== null));
        $this->assertTrue($places->every(fn (Place $place): bool => $place->category instanceof PlaceCategory));
    }

    public function test_event_seeder_creates_ten_dated_events_with_their_city_image()
    {
        $this->seed();

        $events = Event::query()->get();
        $cityImages = City::query()->pluck('image', 'id');

        $this->assertCount(10, $events);
        $this->assertTrue($events->every(fn (Event $event): bool => $event->start_date !== null && $event->end_date !== null));
        $this->assertTrue($events->every(fn (Event $event): bool => $event->image !== null));
        $this->assertTrue($events->every(fn (Event $event): bool => $event->image === $cityImages[$event->city_id]));
    }

    public function test_review_seeder_creates_two_to_four_reviews_for_every_place()
    {
        $this->seed();

        $places = Place::query()->withCount('reviews')->get();

        $this->assertTrue($places->every(
            fn (Place $place): bool => $place->reviews_count >= 2 && $place->reviews_count <= 4
        ));
        $this->assertSame($places->sum('reviews_count'), Review::query()->whereNull('user_id')->count());
    }

    public function test_admin_user_seeder_assigns_the_admin_role()
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->assertTrue($admin->hasRole('admin'));
    }
}
