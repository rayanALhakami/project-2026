<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_receives_arabic_by_default()
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('lang="ar"', false);
        $response->assertSee('dir="rtl"', false);
    }

    public function test_guest_cookie_locale_is_applied()
    {
        $response = $this->withUnencryptedCookie('locale', 'en')->get(route('home'));

        $response->assertOk();
        $response->assertSee('lang="en"', false);
        $response->assertSee('dir="ltr"', false);
    }

    public function test_authenticated_user_locale_overrides_the_cookie()
    {
        $user = User::factory()->create(['locale' => 'fr']);

        $response = $this->actingAs($user)
            ->withUnencryptedCookie('locale', 'en')
            ->get(route('home'));

        $response->assertOk();
        $response->assertSee('lang="fr"', false);
        $response->assertSee('dir="ltr"', false);
    }

    public function test_guests_cannot_update_the_locale()
    {
        $response = $this->patch(route('locale.update'), [
            'locale' => 'en',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_update_their_locale()
    {
        $user = User::factory()->create(['locale' => 'ar']);

        $response = $this->actingAs($user)->patch(route('locale.update'), [
            'locale' => 'es',
        ]);

        $response->assertRedirect();
        $this->assertSame('es', $user->refresh()->locale);
    }

    public function test_unsupported_locale_is_rejected()
    {
        $user = User::factory()->create(['locale' => 'ar']);

        $response = $this->actingAs($user)->patch(route('locale.update'), [
            'locale' => 'xx',
        ]);

        $response->assertSessionHasErrors('locale');
        $this->assertSame('ar', $user->refresh()->locale);
    }
}
