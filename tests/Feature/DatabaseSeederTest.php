<?php

namespace Tests\Feature;

use App\Enums\PlaceCategory;
use App\Models\City;
use App\Models\Place;
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
}
