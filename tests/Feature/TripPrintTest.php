<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TripPrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_print_page(): void
    {
        $trip = Trip::factory()->create();

        $this->get(route('trips.print', $trip))->assertRedirect(route('login'));
    }

    public function test_non_owners_cannot_open_the_print_page(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $trip = Trip::factory()->for($owner)->create();

        $this->actingAs($other)
            ->get(route('trips.print', $trip))
            ->assertForbidden();
    }

    public function test_the_owner_can_open_the_print_page(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->get(route('trips.print', $trip))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('TripPrint')
                ->where('trip.id', $trip->id)
                ->where('trip.title', $trip->title)
            );
    }
}
