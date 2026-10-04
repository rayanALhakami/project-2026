<?php

namespace Tests\Feature;

use App\Ai\Agents\TouristGuide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Ai\Prompts\AgentPrompt;
use RuntimeException;
use Tests\TestCase;

class AssistantChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_chat_with_the_assistant(): void
    {
        TouristGuide::fake(['أهلاً بك في السعودية!']);

        $response = $this->postJson(route('assistant.chat'), ['message' => 'مرحبا']);

        $response->assertOk()
            ->assertExactJson([
                'reply' => 'أهلاً بك في السعودية!',
                'conversation_id' => null,
                'user_message_id' => null,
                'assistant_message_id' => null,
            ]);

        TouristGuide::assertPrompted('مرحبا');
    }

    public function test_the_message_is_required_before_prompting_the_agent(): void
    {
        TouristGuide::fake();

        $this->postJson(route('assistant.chat'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('message');

        $this->postJson(route('assistant.chat'), ['message' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('message');

        TouristGuide::assertNeverPrompted();
    }

    public function test_authenticated_users_can_chat_with_the_assistant(): void
    {
        TouristGuide::fake(['تم استلام رسالتك.']);

        $this->actingAs(User::factory()->create())
            ->postJson(route('assistant.chat'), ['message' => 'مرحبا'])
            ->assertOk()
            ->assertJsonPath('reply', 'تم استلام رسالتك.')
            ->assertJsonMissingPaths(['toolCalls', 'tool_calls', 'usage', 'provider', 'model']);

        TouristGuide::assertPrompted('مرحبا');
    }

    public function test_an_attached_photo_is_forwarded_for_landmark_recognition(): void
    {
        TouristGuide::fake(['المعلم في الصورة هو قلعة المصمك.']);

        $response = $this->post(
            route('assistant.chat'),
            [
                'message' => 'وش هذا المعلم؟',
                'image' => UploadedFile::fake()->image('landmark.png'),
            ],
            ['Accept' => 'application/json'],
        );

        $response->assertOk()
            ->assertJsonPath('reply', 'المعلم في الصورة هو قلعة المصمك.');

        TouristGuide::assertPrompted(
            fn (AgentPrompt $prompt): bool => $prompt->contains('تعرّف على المعلم')
                && $prompt->contains('وش هذا المعلم؟')
                && $prompt->attachments->count() === 1,
        );
    }

    public function test_provider_failures_return_a_generic_arabic_error(): void
    {
        TouristGuide::fake(fn () => throw new RuntimeException('Provider exploded with secret detail.'));

        $response = $this->postJson(route('assistant.chat'), ['message' => 'مرحبا']);

        $response->assertStatus(503)
            ->assertJsonStructure(['error'])
            ->assertJsonMissingPaths(['exception', 'trace', 'file', 'line']);

        $this->assertStringNotContainsString('Provider exploded', $response->getContent());
        $this->assertStringNotContainsString('RuntimeException', $response->getContent());
    }
}
