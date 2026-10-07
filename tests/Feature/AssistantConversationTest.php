<?php

namespace Tests\Feature;

use App\Ai\Agents\TouristGuide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Ai\Enums\MessageStatus;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Models\ConversationMessage;
use Tests\TestCase;

class AssistantConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_the_conversations_index(): void
    {
        $this->get(route('assistant.conversations.index'))
            ->assertRedirect(route('login'));
    }

    public function test_users_only_see_their_own_conversations(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $conversation = $this->conversationFor($user, ['title' => 'رحلة الرياض']);
        $this->messageFor($conversation, 'user', 'مرحبا');
        $this->messageFor($conversation, 'assistant', 'أهلاً بك');
        $this->conversationFor($other, ['title' => 'محادثة أخرى']);

        $this->actingAs($user)
            ->getJson(route('assistant.conversations.index'))
            ->assertOk()
            ->assertJsonCount(1, 'conversations')
            ->assertJsonPath('conversations.0.id', $conversation->id)
            ->assertJsonPath('conversations.0.title', 'رحلة الرياض')
            ->assertJsonPath('conversations.0.messages_count', 2);
    }

    public function test_users_can_view_the_messages_of_their_conversation(): void
    {
        $user = User::factory()->create();
        $conversation = $this->conversationFor($user);

        $this->messageFor($conversation, 'user', 'خطة لثلاثة أيام');
        $this->messageFor($conversation, 'assistant', 'تفضل خطتك');

        $this->actingAs($user)
            ->getJson(route('assistant.conversations.show', $conversation->id))
            ->assertOk()
            ->assertJsonCount(2, 'messages')
            ->assertJsonPath('conversation.id', $conversation->id)
            ->assertJsonPath('messages.0.role', 'user')
            ->assertJsonPath('messages.0.content', 'خطة لثلاثة أيام')
            ->assertJsonPath('messages.1.role', 'assistant')
            ->assertJsonPath('messages.1.content', 'تفضل خطتك');
    }

    public function test_users_cannot_view_another_users_conversation(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $conversation = $this->conversationFor($other, ['title' => 'محادثة خاصة']);
        $this->messageFor($conversation, 'user', 'سر');

        $this->actingAs($user)
            ->getJson(route('assistant.conversations.show', $conversation->id))
            ->assertNotFound();
    }

    public function test_users_can_delete_their_conversation_and_its_messages(): void
    {
        $user = User::factory()->create();
        $conversation = $this->conversationFor($user);
        $this->messageFor($conversation, 'user', 'مرحبا');

        $this->actingAs($user)
            ->deleteJson(route('assistant.conversations.destroy', $conversation->id))
            ->assertOk()
            ->assertExactJson(['deleted' => true]);

        $this->assertDatabaseMissing('agent_conversations', ['id' => $conversation->id]);
        $this->assertDatabaseMissing('agent_conversation_messages', ['conversation_id' => $conversation->id]);
    }

    public function test_users_cannot_delete_another_users_conversation(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $conversation = $this->conversationFor($other);

        $this->actingAs($user)
            ->deleteJson(route('assistant.conversations.destroy', $conversation->id))
            ->assertNotFound();

        $this->assertDatabaseHas('agent_conversations', ['id' => $conversation->id]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function conversationFor(User $user, array $attributes = []): Conversation
    {
        return Conversation::query()->create([
            'id' => (string) Str::uuid7(),
            'participant_type' => Conversation::participantType($user),
            'participant_id' => Conversation::participantKey($user),
            'title' => 'محادثة تجريبية',
            ...$attributes,
        ]);
    }

    private function messageFor(Conversation $conversation, string $role, string $content): ConversationMessage
    {
        return ConversationMessage::query()->create([
            'id' => (string) Str::uuid7(),
            'conversation_id' => $conversation->id,
            'participant_type' => $conversation->participant_type,
            'participant_id' => $conversation->participant_id,
            'agent' => TouristGuide::class,
            'role' => $role,
            'content' => $content,
            'attachments' => '[]',
            'steps' => '[]',
            'usage' => '[]',
            'meta' => '[]',
            'status' => MessageStatus::Completed,
        ]);
    }
}
