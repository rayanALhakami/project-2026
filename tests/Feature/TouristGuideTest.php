<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TouristGuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_visit_the_traveler_guide_page()
    {
        $this->get(route('guide'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Guide'));
    }

    public function test_sitemap_lists_the_main_public_pages()
    {
        $response = $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml');

        foreach (['home', 'places', 'assistant', 'translate', 'guide'] as $name) {
            $response->assertSee(e(url()->route($name)), false);
        }
    }
}
