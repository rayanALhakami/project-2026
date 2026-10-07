<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_favorite_endpoints(): void
    {
        $place = Place::factory()->create();

        $this->get(route('favorites.index'))->assertRedirect(route('login'));
        $this->post(route('favorites.toggle', $place))->assertRedirect(route('login'));
        $this->post(route('favorites.sync'), ['ids' => []])->assertRedirect(route('login'));
    }

    public function test_guests_are_redirected_from_the_favorites_page(): void
    {
        $this->get(route('favorites'))->assertRedirect(route('login'));
    }

    public function test_users_can_visit_the_favorites_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('favorites'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Favorites'));
    }

    public function test_users_see_their_favorite_ids_sorted(): void
    {
        $user = User::factory()->create();
        $places = Place::factory()->count(3)->create();

        Favorite::factory()->for($user)->for($places[2])->create();
        Favorite::factory()->for($user)->for($places[0])->create();

        $this->actingAs($user)
            ->getJson(route('favorites.index'))
            ->assertOk()
            ->assertExactJson([
                'ids' => [$places[0]->id, $places[2]->id],
            ]);
    }

    public function test_toggling_a_place_adds_then_removes_it(): void
    {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.toggle', $place))
            ->assertOk()
            ->assertExactJson([
                'favorited' => true,
                'ids' => [$place->id],
            ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'place_id' => $place->id,
        ]);

        $this->actingAs($user)
            ->postJson(route('favorites.toggle', $place))
            ->assertOk()
            ->assertExactJson([
                'favorited' => false,
                'ids' => [],
            ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'place_id' => $place->id,
        ]);
        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_toggling_the_same_place_twice_never_duplicates(): void
    {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $this->actingAs($user)->postJson(route('favorites.toggle', $place))->assertOk();
        $this->actingAs($user)->postJson(route('favorites.toggle', $place))->assertOk();
        $this->actingAs($user)->postJson(route('favorites.toggle', $place))->assertOk();

        $this->assertDatabaseCount('favorites', 1);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'place_id' => $place->id,
        ]);
    }

    public function test_sync_merges_guest_ids_with_existing_favorites(): void
    {
        $user = User::factory()->create();
        $places = Place::factory()->count(3)->create();

        Favorite::factory()->for($user)->for($places[0])->create();

        $this->actingAs($user)
            ->postJson(route('favorites.sync'), [
                'ids' => [$places[1]->id, $places[2]->id, $places[1]->id],
            ])
            ->assertOk()
            ->assertExactJson([
                'ids' => [$places[0]->id, $places[1]->id, $places[2]->id],
            ]);

        $this->assertDatabaseCount('favorites', 3);
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'place_id' => $places[1]->id]);
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'place_id' => $places[2]->id]);
    }

    public function test_sync_with_empty_ids_returns_current_ids(): void
    {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        Favorite::factory()->for($user)->for($place)->create();

        $this->actingAs($user)
            ->postJson(route('favorites.sync'), ['ids' => []])
            ->assertOk()
            ->assertExactJson([
                'ids' => [$place->id],
            ]);

        $this->assertDatabaseCount('favorites', 1);
    }

    public function test_sync_rejects_unknown_place_ids(): void
    {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        Favorite::factory()->for($user)->for($place)->create();

        $this->actingAs($user)
            ->postJson(route('favorites.sync'), ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);

        $this->assertDatabaseCount('favorites', 1);
        $this->assertDatabaseMissing('favorites', ['place_id' => 99999]);
    }

    public function test_favorites_are_isolated_between_users(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $place = Place::factory()->create();

        $this->actingAs($userA)->postJson(route('favorites.toggle', $place))->assertOk();

        $this->actingAs($userB)
            ->getJson(route('favorites.index'))
            ->assertOk()
            ->assertExactJson(['ids' => []]);

        $this->actingAs($userB)
            ->postJson(route('favorites.toggle', $place))
            ->assertOk()
            ->assertExactJson([
                'favorited' => true,
                'ids' => [$place->id],
            ]);

        $this->assertDatabaseCount('favorites', 2);
        $this->assertDatabaseHas('favorites', ['user_id' => $userA->id, 'place_id' => $place->id]);
        $this->assertDatabaseHas('favorites', ['user_id' => $userB->id, 'place_id' => $place->id]);
    }
}
