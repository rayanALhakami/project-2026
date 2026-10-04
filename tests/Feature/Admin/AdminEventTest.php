<?php

namespace Tests\Feature\Admin;

use App\Models\City;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_create_an_event(): void
    {
        $admin = User::factory()->admin()->create();
        $city = City::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.events.store'), [
                'city_id' => $city->id,
                'name' => 'Saudi Founding Day',
                'description' => 'A national celebration.',
                'start_date' => '2026-11-10',
                'end_date' => '2026-11-12',
                'url' => 'https://example.com/events/founding-day',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('events', [
            'city_id' => $city->id,
            'name' => 'Saudi Founding Day',
            'start_date' => '2026-11-10 00:00:00',
            'end_date' => '2026-11-12 00:00:00',
        ]);
    }

    public function test_admins_can_update_an_event(): void
    {
        $admin = User::factory()->admin()->create();
        $city = City::factory()->create();
        $otherCity = City::factory()->create();
        $event = Event::factory()->create(['city_id' => $city->id]);

        $this->actingAs($admin)
            ->put(route('admin.events.update', $event), [
                'city_id' => $otherCity->id,
                'name' => 'Updated Event',
                'start_date' => '2026-12-01',
                'end_date' => '2026-12-05',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'city_id' => $otherCity->id,
            'name' => 'Updated Event',
            'start_date' => '2026-12-01 00:00:00',
            'end_date' => '2026-12-05 00:00:00',
        ]);
    }

    public function test_admins_can_delete_an_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.events.destroy', $event))
            ->assertRedirect();

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_event_validation_rejects_bad_dates_and_cities(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.events.store'), [
                'city_id' => 99999,
                'name' => 'Invalid Event',
                'start_date' => '2026-11-10',
                'end_date' => '2026-11-01',
            ])
            ->assertSessionHasErrors(['end_date', 'city_id']);

        $this->assertDatabaseCount('events', 0);
    }

    public function test_regular_users_cannot_manage_events(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        $event = Event::factory()->create(['city_id' => $city->id]);

        $payload = [
            'city_id' => $city->id,
            'name' => 'Attempted Event',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-12',
        ];

        $this->actingAs($user)->post(route('admin.events.store'), $payload)->assertForbidden();
        $this->actingAs($user)->put(route('admin.events.update', $event), $payload)->assertForbidden();
        $this->actingAs($user)->delete(route('admin.events.destroy', $event))->assertForbidden();

        $this->assertDatabaseCount('events', 1);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => $event->name,
        ]);
    }
}
