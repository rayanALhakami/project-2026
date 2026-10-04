<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }

    public function test_index_returns_the_notifications_newest_first_with_iso_dates_and_unread_count(): void
    {
        $this->travelTo('2026-01-15 10:30:00');

        $user = User::factory()->create();

        $older = Notification::factory()->for($user)->create([
            'type' => 'nearby_event',
            'title' => 'فعالية قريبة',
            'body' => 'تبدأ بعد أيام',
            'data' => ['event_id' => 3],
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        $newer = Notification::factory()->for($user)->read()->create([
            'type' => 'trip_reminder',
            'title' => 'رحلتك قريبة',
            'body' => 'تبدأ غداً',
            'data' => ['trip_id' => 5],
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ]);

        $response = $this->actingAs($user)->getJson(route('notifications.index'));

        $response->assertOk()->assertExactJson([
            'notifications' => [
                [
                    'id' => $newer->id,
                    'type' => 'trip_reminder',
                    'title' => 'رحلتك قريبة',
                    'body' => 'تبدأ غداً',
                    'data' => ['trip_id' => 5],
                    'read_at' => '2026-01-15T10:30:00+00:00',
                    'created_at' => '2026-01-15T10:29:00+00:00',
                ],
                [
                    'id' => $older->id,
                    'type' => 'nearby_event',
                    'title' => 'فعالية قريبة',
                    'body' => 'تبدأ بعد أيام',
                    'data' => ['event_id' => 3],
                    'read_at' => null,
                    'created_at' => '2026-01-15T09:30:00+00:00',
                ],
            ],
            'unread_count' => 1,
        ]);
    }

    public function test_index_returns_at_most_twenty_notifications_and_excludes_other_users(): void
    {
        $this->travelTo('2026-01-15 10:30:00');

        $user = User::factory()->create();
        $other = User::factory()->create();

        Notification::factory()->for($user)->count(25)->create();
        $otherNotification = Notification::factory()->for($other)->create();

        $response = $this->actingAs($user)->getJson(route('notifications.index'));

        $response->assertOk()
            ->assertJsonCount(20, 'notifications')
            ->assertJsonPath('unread_count', 25)
            ->assertJsonMissing(['id' => $otherNotification->id]);
    }

    public function test_read_marks_the_notification_and_returns_the_decremented_unread_count(): void
    {
        $user = User::factory()->create();
        $target = Notification::factory()->for($user)->create();
        Notification::factory()->for($user)->create();
        Notification::factory()->for($user)->read()->create();

        $response = $this->actingAs($user)->postJson(route('notifications.read', $target));

        $response->assertOk()->assertExactJson(['unread_count' => 1]);
        $this->assertNotNull($target->fresh()->read_at);
    }

    public function test_read_forbids_another_users_notification(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->for(User::factory())->create();

        $response = $this->actingAs($user)->postJson(route('notifications.read', $notification));

        $response->assertForbidden();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_read_all_marks_only_the_users_notifications_read_and_returns_zero(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Notification::factory()->for($user)->count(2)->create();
        $otherNotification = Notification::factory()->for($other)->create();

        $response = $this->actingAs($user)->postJson(route('notifications.read-all'));

        $response->assertOk()->assertExactJson(['unread_count' => 0]);
        $this->assertDatabaseMissing('notifications', ['user_id' => $user->id, 'read_at' => null]);
        $this->assertDatabaseHas('notifications', ['id' => $otherNotification->id, 'read_at' => null]);
    }
}
