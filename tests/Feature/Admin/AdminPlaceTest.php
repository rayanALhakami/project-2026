<?php

namespace Tests\Feature\Admin;

use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPlaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_update_a_place(): void
    {
        $admin = User::factory()->admin()->create();
        $place = Place::factory()->create([
            'description_en' => 'Original English description',
            'avg_visit_duration' => 90,
        ]);

        $originalCityId = $place->city_id;
        $originalLatitude = $place->latitude;
        $originalLongitude = $place->longitude;

        $this->actingAs($admin)
            ->put(route('admin.places.update', $place), $this->validPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('places', [
            'id' => $place->id,
            'name' => 'Updated Name',
            'name_en' => 'Updated Name EN',
            'category' => 'museum',
            'description' => 'Updated description',
            'ticket_price' => 150,
            'rating' => 4.5,
            'opening_hours' => '10:00 - 20:00',
            'is_indoor' => true,
            'family_friendly' => true,
            'wheelchair_accessible' => false,
            'prayer_facilities' => true,
            'closed_friday' => false,
        ]);

        $place->refresh();

        $this->assertSame($originalCityId, $place->city_id);
        $this->assertSame($originalLatitude, $place->latitude);
        $this->assertSame($originalLongitude, $place->longitude);
        $this->assertSame('Original English description', $place->description_en);
        $this->assertSame(90, $place->avg_visit_duration);
    }

    public function test_update_validates_the_payload(): void
    {
        $admin = User::factory()->admin()->create();
        $place = Place::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.places.update', $place), [
                'name_en' => 'Updated Name EN',
                'category' => 'invalid-category',
                'rating' => 9,
                'is_indoor' => 'not-a-boolean',
                'family_friendly' => true,
                'wheelchair_accessible' => true,
                'prayer_facilities' => true,
                'closed_friday' => false,
            ])
            ->assertSessionHasErrors(['name', 'category', 'rating', 'is_indoor']);

        $this->assertDatabaseHas('places', [
            'id' => $place->id,
            'name' => $place->name,
            'name_en' => $place->name_en,
            'category' => $place->category->value,
            'rating' => $place->getRawOriginal('rating'),
        ]);
    }

    public function test_regular_users_cannot_update_places(): void
    {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.places.update', $place), $this->validPayload())
            ->assertForbidden();

        $this->assertDatabaseHas('places', [
            'id' => $place->id,
            'name' => $place->name,
            'name_en' => $place->name_en,
            'category' => $place->category->value,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'name' => 'Updated Name',
            'name_en' => 'Updated Name EN',
            'category' => 'museum',
            'description' => 'Updated description',
            'ticket_price' => 150,
            'rating' => 4.5,
            'opening_hours' => '10:00 - 20:00',
            'is_indoor' => true,
            'family_friendly' => true,
            'wheelchair_accessible' => false,
            'prayer_facilities' => true,
            'closed_friday' => false,
        ];
    }
}
