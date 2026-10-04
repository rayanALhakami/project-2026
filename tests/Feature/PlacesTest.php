<?php

namespace Tests\Feature;

use App\Enums\PlaceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PlacesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_browse_the_places_page()
    {
        $response = $this->get(route('places'));
        $response->assertOk();
    }

    public function test_authenticated_users_can_visit_the_places_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('places'));
        $response->assertOk();
    }

    public function test_places_page_shares_the_seeded_catalogue()
    {
        $this->seed();

        $this->get(route('places'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Places')
                ->has('cities', 9)
                ->has('places', 28)
                ->whereType('places.0.city_id', 'integer')
                ->where('places.0.category', fn (mixed $category): bool => is_string($category) && PlaceCategory::tryFrom($category) !== null)
                ->where('places.0.ticket_price', '0.00')
                ->where('places.0.rating', '4.7')
                ->whereType('places.0.tags', 'array')
                ->whereType('places.0.latitude', 'double')
                ->whereType('places.0.image', 'string|null')
            );
    }
}
