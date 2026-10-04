<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_visit_public_pages_but_not_protected_ones()
    {
        foreach (['translate', 'assistant'] as $name) {
            $this->get(route($name))->assertOk();
        }

        foreach (['trips'] as $name) {
            $this->get(route($name))->assertRedirect(route('login'));
        }
    }

    public function test_authenticated_users_can_visit_the_pages()
    {
        $this->actingAs(User::factory()->create());

        foreach (['trips', 'translate', 'assistant'] as $name) {
            $this->get(route($name))->assertOk();
        }
    }
}
