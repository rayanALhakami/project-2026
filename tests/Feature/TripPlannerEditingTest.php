<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripPlannerEditingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_the_editing_endpoints(): void
    {
        $day = TripDay::factory()->create();
        $item = TripItem::factory()->create();

        $this->post(route('trip-days.items.store', $day))->assertRedirect(route('login'));
        $this->put(route('trip-items.update', $item))->assertRedirect(route('login'));
        $this->delete(route('trip-items.destroy', $item))->assertRedirect(route('login'));
        $this->post(route('trip-items.move', $item))->assertRedirect(route('login'));
    }

    public function test_users_can_add_a_place_item_to_their_trip_day(): void
    {
        $user = User::factory()->create();
        $day = TripDay::factory()->for(Trip::factory()->for($user))->create();
        $place = Place::factory()->create();

        $this->actingAs($user)
            ->postJson(route('trip-days.items.store', $day), [
                'place_id' => $place->id,
                'start_time' => '10:30',
                'duration_minutes' => 90,
            ])
            ->assertOk()
            ->assertJsonPath('title', $place->name)
            ->assertJsonPath('start_time', '10:30')
            ->assertJsonPath('duration_minutes', 90);

        $this->assertDatabaseHas('trip_items', [
            'trip_day_id' => $day->id,
            'place_id' => $place->id,
            'start_time' => '10:30',
            'duration_minutes' => 90,
        ]);
    }

    public function test_users_cannot_add_items_to_another_users_day(): void
    {
        $user = User::factory()->create();
        $day = TripDay::factory()->create();
        $place = Place::factory()->create();

        $this->actingAs($user)
            ->postJson(route('trip-days.items.store', $day), [
                'place_id' => $place->id,
                'start_time' => '10:30',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('trip_items', 0);
    }

    public function test_adding_an_item_validates_the_payload(): void
    {
        $user = User::factory()->create();
        $day = TripDay::factory()->for(Trip::factory()->for($user))->create();

        $this->actingAs($user)
            ->postJson(route('trip-days.items.store', $day), [
                'start_time' => '25:99',
                'duration_minutes' => -5,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['start_time', 'duration_minutes']);
    }

    public function test_users_can_update_their_item(): void
    {
        $user = User::factory()->create();
        $item = TripItem::factory()
            ->for(TripDay::factory()->for(Trip::factory()->for($user)), 'day')
            ->create(['start_time' => '09:00']);

        $this->actingAs($user)
            ->putJson(route('trip-items.update', $item), [
                'start_time' => '14:45',
                'duration_minutes' => 120,
            ])
            ->assertOk()
            ->assertJsonPath('start_time', '14:45')
            ->assertJsonPath('duration_minutes', 120);

        $this->assertSame('14:45', $item->refresh()->start_time);
        $this->assertSame(120, $item->duration_minutes);
    }

    public function test_users_cannot_update_another_users_item(): void
    {
        $user = User::factory()->create();
        $item = TripItem::factory()->create(['start_time' => '09:00']);

        $this->actingAs($user)
            ->putJson(route('trip-items.update', $item), ['start_time' => '14:45'])
            ->assertForbidden();

        $this->assertSame('09:00', $item->refresh()->start_time);
    }

    public function test_users_can_remove_their_item(): void
    {
        $user = User::factory()->create();
        $item = TripItem::factory()
            ->for(TripDay::factory()->for(Trip::factory()->for($user)), 'day')
            ->create();

        $this->actingAs($user)
            ->deleteJson(route('trip-items.destroy', $item))
            ->assertOk()
            ->assertExactJson(['deleted' => true]);

        $this->assertDatabaseMissing('trip_items', ['id' => $item->id]);
    }

    public function test_users_cannot_remove_another_users_item(): void
    {
        $user = User::factory()->create();
        $item = TripItem::factory()->create();

        $this->actingAs($user)
            ->deleteJson(route('trip-items.destroy', $item))
            ->assertForbidden();

        $this->assertDatabaseHas('trip_items', ['id' => $item->id]);
    }

    public function test_items_can_move_up_and_down_within_the_day(): void
    {
        $user = User::factory()->create();
        $day = TripDay::factory()->for(Trip::factory()->for($user))->create();
        $first = TripItem::factory()->for($day, 'day')->create(['sort_order' => 0]);
        $second = TripItem::factory()->for($day, 'day')->create(['sort_order' => 1]);

        $this->actingAs($user)
            ->postJson(route('trip-items.move', $second), ['direction' => 'up'])
            ->assertOk()
            ->assertJsonPath('moved', true);

        $this->assertSame(0, $second->refresh()->sort_order);
        $this->assertSame(1, $first->refresh()->sort_order);

        $this->actingAs($user)
            ->postJson(route('trip-items.move', $second), ['direction' => 'down'])
            ->assertOk()
            ->assertJsonPath('moved', true);

        $this->assertSame(1, $second->refresh()->sort_order);
        $this->assertSame(0, $first->refresh()->sort_order);
    }

    public function test_moving_past_the_edge_reports_no_move(): void
    {
        $user = User::factory()->create();
        $day = TripDay::factory()->for(Trip::factory()->for($user))->create();
        $item = TripItem::factory()->for($day, 'day')->create(['sort_order' => 0]);

        $this->actingAs($user)
            ->postJson(route('trip-items.move', $item), ['direction' => 'up'])
            ->assertOk()
            ->assertExactJson(['moved' => false]);

        $this->assertSame(0, $item->refresh()->sort_order);
    }
}
