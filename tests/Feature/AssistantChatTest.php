<?php

namespace Tests\Feature;

use App\Ai\Agents\TouristGuide;
use App\Ai\GuestParticipant;
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
        config(['ai.conversations.generate_title' => false]);

        TouristGuide::fake(['أهلاً بك في السعودية!']);

        $response = $this->postJson(route('assistant.chat'), ['message' => 'مرحبا']);

        $response->assertOk()
            ->assertJsonPath('reply', 'أهلاً بك في السعودية!');

        $conversationId = $response->json('conversation_id');

        $this->assertIsString($conversationId);
        $this->assertDatabaseHas('agent_conversations', [
            'id' => $conversationId,
            'participant_type' => GuestParticipant::class,
        ]);

        TouristGuide::assertPrompted('مرحبا');
    }

    public function test_guests_can_continue_their_conversation(): void
    {
        config(['ai.conversations.generate_title' => false]);

        TouristGuide::fake(['مقارنة جدة وأبها', 'ملخص موجز']);

        $first = $this->postJson(route('assistant.chat'), ['message' => 'قارن بين جدة وأبها'])->assertOk();

        $conversationId = $first->json('conversation_id');
        $this->assertIsString($conversationId);

        $second = $this->postJson(route('assistant.chat'), [
            'message' => 'اخصر',
            'conversation_id' => $conversationId,
        ])->assertOk()->assertJsonPath('reply', 'ملخص موجز');

        $this->assertSame($conversationId, $second->json('conversation_id'));
        $this->assertSame(2, DB::table('agent_conversation_messages')
            ->where('conversation_id', $conversationId)
            ->where('role', 'user')
            ->count());
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

    public function test_provider_failures_return_a_fallback_reply_without_exposing_details(): void
    {
        TouristGuide::fake(fn () => throw new RuntimeException('Provider exploded with secret detail.'));

        $response = $this->postJson(route('assistant.chat'), ['message' => 'مرحبا']);

        $response->assertOk()
            ->assertJsonPath('meta.fallback', true)
            ->assertJsonPath('meta.cached', false)
            ->assertJsonStructure(['reply', 'meta' => ['cached', 'fallback', 'response_ms']])
            ->assertJsonMissingPaths(['exception', 'trace', 'file', 'line']);

        $this->assertStringNotContainsString('Provider exploded', $response->getContent());
        $this->assertStringNotContainsString('RuntimeException', $response->getContent());
    }

    public function test_duplicate_messages_are_served_from_short_lived_cache(): void
    {
        config(['ai.conversations.generate_title' => false]);

        TouristGuide::fake(['رد أول']);

        $first = $this->postJson(route('assistant.chat'), ['message' => 'وش أفضل مكان للقهوة في الرياض؟'])
            ->assertOk()
            ->assertJsonPath('meta.cached', false);

        $conversationId = $first->json('conversation_id');

        $this->assertIsString($conversationId);

        $this->postJson(route('assistant.chat'), ['message' => 'وش أفضل مكان للقهوة في الرياض؟'])
            ->assertOk()
            ->assertJsonPath('reply', 'رد أول')
            ->assertJsonPath('meta.cached', true)
            ->assertJsonPath('meta.fallback', false)
            ->assertJsonPath('conversation_id', $conversationId);

        TouristGuide::assertPromptedTimes(1);
    }

    public function test_image_requests_still_return_service_unavailable_when_provider_fails(): void
    {
        TouristGuide::fake(fn () => throw new RuntimeException('Provider exploded with secret detail.'));

        $response = $this->post(
            route('assistant.chat'),
            [
                'message' => 'وش هذا المعلم؟',
                'image' => UploadedFile::fake()->image('landmark.png'),
            ],
            ['Accept' => 'application/json'],
        );

        $response->assertStatus(503)
            ->assertJsonStructure(['error'])
            ->assertJsonPath('meta.fallback', true);
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

    public function test_a_conversation_owned_by_a_guest_is_not_continued_by_a_user(): void
    {
        config(['ai.conversations.generate_title' => false]);

        TouristGuide::fake(['رد الزائر', 'رد المستخدم']);

        $guestResponse = $this->postJson(route('assistant.chat'), ['message' => 'مرحبا'])->assertOk();

        $guestConversationId = $guestResponse->json('conversation_id');
        $this->assertIsString($guestConversationId);

        $userResponse = $this->actingAs(User::factory()->create())
            ->postJson(route('assistant.chat'), [
                'message' => 'أكمل محادثة الزائر',
                'conversation_id' => $guestConversationId,
            ])
            ->assertOk()
            ->assertJsonPath('reply', 'رد المستخدم');

        $this->assertIsString($userResponse->json('conversation_id'));
        $this->assertNotSame($guestConversationId, $userResponse->json('conversation_id'));
        $this->assertSame(1, DB::table('agent_conversation_messages')
            ->where('conversation_id', $guestConversationId)
            ->where('role', 'user')
            ->count());
    }
}
