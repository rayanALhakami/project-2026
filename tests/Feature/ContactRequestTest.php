<?php

namespace Tests\Feature;

use App\Ai\Agents\TouristGuide;
use App\Models\City;
use App\Models\ContactRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Support\SessionKey;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Ai\Prompts\AgentPrompt;
use RuntimeException;
use Tests\TestCase;

class ContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_submit_a_planning_request(): void
    {
        $city = City::factory()->create();

        $response = $this->post(route('contact.store'), [
            'name' => 'سعود القحطاني',
            'phone' => '0551234567',
            'city_id' => $city->id,
            'start_date' => now()->addWeek()->toDateString(),
            'travelers' => 4,
            'budget' => 5000,
            'notes' => 'رحلة عائلية إلى العلا',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('contact_requests', [
            'name' => 'سعود القحطاني',
            'phone' => '0551234567',
            'city_id' => $city->id,
            'travelers' => 4,
            'handled_at' => null,
        ]);

        $contactRequest = ContactRequest::query()->firstOrFail();

        $this->assertNotNull($contactRequest->token);

        $response->assertSessionHas(SessionKey::FLASH_DATA, [
            'planRequest' => ['token' => $contactRequest->token],
        ]);
    }

    public function test_planning_request_is_validated(): void
    {
        $this->post(route('contact.store'), [])
            ->assertSessionHasErrors(['name', 'phone']);

        $this->post(route('contact.store'), [
            'name' => 'سعود القحطاني',
            'phone' => '0551234567',
            'city_id' => 99999,
            'start_date' => now()->subDay()->toDateString(),
            'travelers' => 0,
            'budget' => -5,
        ])->assertSessionHasErrors(['city_id', 'start_date', 'travelers', 'budget']);

        $this->assertDatabaseCount('contact_requests', 0);
    }

    public function test_the_smart_guide_generates_a_plan_for_a_planning_request(): void
    {
        $city = City::factory()->create(['name' => 'الرياض', 'name_en' => 'Riyadh']);
        $contactRequest = ContactRequest::factory()->create([
            'city_id' => $city->id,
            'budget' => 5000,
            'notes' => 'رحلة عائلية مع أطفال',
        ]);

        TouristGuide::fake(['**اليوم الأول** زيارة المتحف.']);

        $this->postJson(route('contact.plan', $contactRequest->token))
            ->assertStatus(202)
            ->assertJsonPath('status', 'pending');

        $contactRequest->refresh();

        $this->assertSame('**اليوم الأول** زيارة المتحف.', $contactRequest->plan);
        $this->assertNotNull($contactRequest->plan_generated_at);

        TouristGuide::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->contains('الرياض')
            && $prompt->contains('رحلة عائلية مع أطفال')
            && $prompt->contains('5,000'));

        $this->postJson(route('contact.plan', $contactRequest->token))
            ->assertOk()
            ->assertJsonPath('status', 'ready')
            ->assertJsonPath('plan', '**اليوم الأول** زيارة المتحف.');

        TouristGuide::assertPromptedTimes(1);
    }

    public function test_a_stored_plan_is_returned_without_prompting_the_guide_again(): void
    {
        $contactRequest = ContactRequest::factory()->create([
            'plan' => 'خطة محفوظة مسبقاً',
            'plan_generated_at' => now(),
        ]);

        TouristGuide::fake();

        $this->postJson(route('contact.plan', $contactRequest->token))
            ->assertOk()
            ->assertJsonPath('status', 'ready')
            ->assertJsonPath('plan', 'خطة محفوظة مسبقاً');

        TouristGuide::assertNeverPrompted();
    }

    public function test_a_failed_generation_is_retried_on_the_next_request(): void
    {
        $contactRequest = ContactRequest::factory()->create();

        $attempts = 0;

        TouristGuide::fake(function () use (&$attempts): string {
            $attempts++;

            if ($attempts === 1) {
                throw new RuntimeException('Provider exploded with secret detail.');
            }

            return 'خطة بديلة';
        });

        $this->postJson(route('contact.plan', $contactRequest->token))
            ->assertStatus(202);

        $this->assertNull($contactRequest->fresh()->plan);

        $this->postJson(route('contact.plan', $contactRequest->token))
            ->assertStatus(202);

        $this->assertSame('خطة بديلة', $contactRequest->fresh()->plan);
    }

    public function test_plan_generation_returns_a_404_for_an_unknown_token(): void
    {
        TouristGuide::fake();

        $this->postJson(route('contact.plan', (string) Str::uuid()))->assertNotFound();

        TouristGuide::assertNeverPrompted();
    }

    public function test_admins_can_view_and_manage_requests(): void
    {
        $admin = User::factory()->admin()->create();
        ContactRequest::factory()->count(2)->create();

        $this->actingAs($admin)
            ->get(route('admin.requests.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Requests')
                ->has('requests', 2)
            );

        $contactRequest = ContactRequest::query()->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.requests.handle', $contactRequest))
            ->assertRedirect();

        $this->assertNotNull($contactRequest->fresh()->handled_at);

        $this->actingAs($admin)
            ->patch(route('admin.requests.handle', $contactRequest))
            ->assertRedirect();

        $this->assertNull($contactRequest->fresh()->handled_at);

        $this->actingAs($admin)
            ->delete(route('admin.requests.destroy', $contactRequest))
            ->assertRedirect();

        $this->assertDatabaseMissing('contact_requests', ['id' => $contactRequest->id]);
    }

    public function test_regular_users_cannot_manage_requests(): void
    {
        $user = User::factory()->create();
        $contactRequest = ContactRequest::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.requests.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('admin.requests.handle', $contactRequest))
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('admin.requests.destroy', $contactRequest))
            ->assertForbidden();

        $this->assertDatabaseHas('contact_requests', ['id' => $contactRequest->id]);
    }

    public function test_guests_are_redirected_from_admin_requests(): void
    {
        $contactRequest = ContactRequest::factory()->create();

        $this->get(route('admin.requests.index'))->assertRedirect(route('login'));
        $this->patch(route('admin.requests.handle', $contactRequest))->assertRedirect(route('login'));
        $this->delete(route('admin.requests.destroy', $contactRequest))->assertRedirect(route('login'));
    }
}
