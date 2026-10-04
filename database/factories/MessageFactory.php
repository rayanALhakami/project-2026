<?php

namespace Database\Factories;

use App\Enums\MessageRole;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'role' => MessageRole::User,
            'content' => fake()->paragraph(),
            'tool_calls' => null,
            'audio_url' => null,
            'language' => 'ar',
        ];
    }

    /**
     * Indicate that the message was written by the assistant.
     */
    public function assistant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => MessageRole::Assistant,
        ]);
    }
}
