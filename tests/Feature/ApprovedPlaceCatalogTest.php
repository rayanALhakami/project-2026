<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\Review;
use App\Models\TripItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovedPlaceCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_preserve_trip_items_without_transferring_old_reviews(): void
    {
        $museum = Place::factory()->create(['name_en' => 'National Museum']);
        $park = Place::factory()->create(['name_en' => 'New Abha Park']);
        $castle = Place::factory()->create(['name_en' => 'Ottoman Castle']);
        $restaurant = Place::factory()->create(['name_en' => 'Al Baik']);
        $review = Review::factory()->create(['place_id' => $park->id]);
        $item = TripItem::factory()->create(['place_id' => $park->id]);
        $migration = require database_path('migrations/2026_11_18_082329_apply_approved_place_catalog_updates.php');
        $migration->up();
        $migration->up();
        $this->assertSame('/images/places/saudi-national-museum.jpg', $museum->fresh()->image);
        $this->assertSame('Al Dosariyah Castle', $castle->fresh()->name_en);
        $this->assertModelMissing($park);
        $this->assertModelMissing($restaurant);
        $this->assertModelMissing($review);
        $this->assertNull($item->fresh()->place_id);
        $art = Place::where('name_en', 'Art Street')->sole();
        $this->assertSame($park->city_id, $art->city_id);
        $this->assertNull($art->ticket_price);
        $this->assertSame(0, $art->reviews()->count());
        $this->assertFileExists(public_path(ltrim($art->image, '/')));
    }
}
