<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WelcomeClassicTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_classic_page_is_displayed(): void
    {
        $this->get(route('welcome-classic'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('WelcomeClassic'));
    }
}
