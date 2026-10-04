<?php

namespace Tests\Feature\Admin;

use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $place = Place::factory()->create();

        foreach ($this->adminGetRoutes($place) as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_regular_users_are_forbidden(): void
    {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        foreach ($this->adminGetRoutes($place) as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    public function test_admins_can_open_every_admin_page(): void
    {
        $admin = User::factory()->admin()->create();
        $place = Place::factory()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->has('stats')
            );

        $this->actingAs($admin)->get(route('admin.places.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Places')
                ->has('places')
            );

        $this->actingAs($admin)->get(route('admin.places.edit', $place))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/PlaceEdit')
                ->where('place.id', $place->id)
            );

        $this->actingAs($admin)->get(route('admin.events.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Events')
                ->has('events')
            );

        $this->actingAs($admin)->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Reviews')
                ->has('reviews')
            );
    }

    public function test_the_shared_auth_user_exposes_the_admin_flag(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('auth.user.is_admin', true)
            );

        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('auth.user.is_admin', false)
            );
    }

    /**
     * @return array<int, string>
     */
    private function adminGetRoutes(Place $place): array
    {
        return [
            route('admin.dashboard'),
            route('admin.places.index'),
            route('admin.places.edit', $place),
            route('admin.events.index'),
            route('admin.reviews.index'),
        ];
    }
}
