<?php

namespace Tests\Feature;

use App\Ai\Agents\TouristGuide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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

    public function test_a_conversation_owned_by_another_user_forces_a_new_conversation(): void
    {
        config(['ai.conversations.generate_title' => false]);

        TouristGuide::fake(['رد المالك', 'رد المستخدم الآخر']);

        $owner = User::factory()->create();
        $other = User::factory()->create();

        $ownerResponse = $this->actingAs($owner)
            ->postJson(route('assistant.chat'), ['message' => 'مرحبا'])
            ->assertOk()
            ->assertJsonPath('reply', 'رد المالك');

        $ownerConversationId = $ownerResponse->json('conversation_id');

        $this->assertIsString($ownerConversationId);
        $this->assertDatabaseHas('agent_conversations', [
            'id' => $ownerConversationId,
            'participant_type' => $owner->getMorphClass(),
            'participant_id' => $owner->id,
        ]);

        $otherResponse = $this->actingAs($other)
            ->postJson(route('assistant.chat'), [
                'message' => 'أكمل محادثة غيري',
                'conversation_id' => $ownerConversationId,
            ])
            ->assertOk()
            ->assertJsonPath('reply', 'رد المستخدم الآخر');

        $otherConversationId = $otherResponse->json('conversation_id');

        $this->assertIsString($otherConversationId);
        $this->assertNotSame($ownerConversationId, $otherConversationId);
        $this->assertDatabaseHas('agent_conversations', [
            'id' => $otherConversationId,
            'participant_type' => $other->getMorphClass(),
            'participant_id' => $other->id,
        ]);

        $this->assertSame(1, DB::table('agent_conversation_messages')
            ->where('conversation_id', $ownerConversationId)
            ->where('role', 'user')
            ->count());

        $this->assertSame(1, DB::table('agent_conversation_messages')
            ->where('conversation_id', $otherConversationId)
            ->where('role', 'user')
            ->count());
    }
}
