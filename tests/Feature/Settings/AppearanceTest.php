<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('appearance.edit'))->assertRedirect(route('login'));
    }

    public function test_appearance_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('appearance.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('settings/Appearance'));
    }
}
