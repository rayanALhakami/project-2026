<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SharedTripTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_public_trip_can_be_viewed_by_a_guest(): void
    {
        $trip = Trip::factory()->public()->create();

        $day = TripDay::factory()->create(['trip_id' => $trip->id]);
        TripItem::factory()->create(['trip_day_id' => $day->id]);

        $this->get(route('shared.show', $trip->share_token))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('SharedTrip')
                ->where('trip.id', $trip->id)
                ->where('trip.title', $trip->title)
                ->where('trip.days_count', 1)
                ->where('trip.items_count', 1)
            );
    }

    public function test_a_private_trip_with_a_token_is_not_viewable(): void
    {
        $trip = Trip::factory()->create([
            'share_token' => Str::random(32),
            'is_public' => false,
        ]);

        $this->get(route('shared.show', $trip->share_token))->assertNotFound();
    }

    public function test_an_unknown_token_returns_not_found(): void
    {
        $this->get(route('shared.show', 'does-not-exist'))->assertNotFound();
    }

    public function test_the_owner_can_enable_sharing(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->for($owner)->create();

        $response = $this->actingAs($owner)->post(route('trips.share', $trip));

        $response->assertOk()->assertJson(['is_public' => true]);

        $this->assertNotNull($trip->fresh()->share_token);
        $this->assertTrue($trip->fresh()->is_public);
        $this->assertSame(
            route('shared.show', $trip->fresh()->share_token),
            $response->json('share_url'),
        );

        $this->get($response->json('share_url'))->assertOk();
    }

    public function test_sharing_twice_reuses_the_same_token(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->for($owner)->create();

        $first = $this->actingAs($owner)->post(route('trips.share', $trip));
        $second = $this->actingAs($owner)->post(route('trips.share', $trip));

        $token = $first->json('share_token');

        $this->assertNotNull($token);
        $this->assertSame($token, $second->json('share_token'));
        $this->assertSame($token, $trip->fresh()->share_token);
    }

    public function test_the_owner_can_disable_sharing(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::factory()->for($owner)->public()->create();

        $this->actingAs($owner)
            ->post(route('trips.unshare', $trip))
            ->assertOk()
            ->assertJson(['is_public' => false]);

        $this->assertFalse($trip->fresh()->is_public);

        $this->get(route('shared.show', $trip->share_token))->assertNotFound();
    }

    public function test_guests_cannot_share_or_unshare(): void
    {
        $trip = Trip::factory()->create();

        $this->post(route('trips.share', $trip))->assertRedirect(route('login'));
        $this->post(route('trips.unshare', $trip))->assertRedirect(route('login'));
    }

    public function test_non_owners_cannot_share_or_unshare(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $trip = Trip::factory()->for($owner)->public()->create();

        $token = $trip->share_token;

        $this->actingAs($other)->post(route('trips.share', $trip))->assertForbidden();
        $this->actingAs($other)->post(route('trips.unshare', $trip))->assertForbidden();

        $trip->refresh();

        $this->assertTrue($trip->is_public);
        $this->assertSame($token, $trip->share_token);
    }
}
