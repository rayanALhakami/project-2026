<?php

namespace App\Http\Controllers;

use App\Ai\Agents\TouristGuide;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\ConversationStore;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Files\Image;
use Laravel\Ai\Models\Conversation;
use Throwable;

class AssistantController extends Controller
{
    /**
     * Answer a tourist-guide chat message, optionally with an attached landmark photo.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'conversation_id' => ['nullable', 'string', 'max:64'],
            'image' => ['nullable', 'image', 'max:8192'],
        ]);

        try {
            $prompt = $validated['message'];
            $attachments = [];

            if ($request->hasFile('image')) {
                $attachments[] = Image::fromUpload($request->file('image'));
                $prompt = 'تعرّف على المعلم الظاهر في الصورة المرفقة واذكر اسمه ومعلوماته من قاعدة البيانات. سؤال المستخدم: '.$validated['message'];
            }

            $agent = new TouristGuide;
            $user = $request->user();

            $provider = $attachments === [] ? null : Lab::Gemini;

            $response = $user instanceof User
                ? $agent->continueOrStart($this->ownedConversationId($validated['conversation_id'] ?? null, $user), as: $user)
                    ->prompt($prompt, attachments: $attachments, provider: $provider)
                : $agent->prompt($prompt, attachments: $attachments, provider: $provider);

            return response()->json([
                'reply' => (string) $response,
                'conversation_id' => $response->conversationId,
                'user_message_id' => $response->userMessageId,
                'assistant_message_id' => $response->assistantMessageId,
            ]);
        } catch (Throwable $exception) {
            Log::error('Assistant chat request failed.', ['exception' => $exception]);

            return response()->json([
                'error' => 'عذراً، حدث خطأ أثناء معالجة طلبك. حاول مرة أخرى بعد قليل.',
            ], 503);
        }
    }

    /**
     * Only continue a conversation owned by the given user; otherwise start a new one.
     */
    private function ownedConversationId(?string $conversationId, User $user): ?string
    {
        if ($conversationId === null) {
            return null;
        }

        $store = resolve(ConversationStore::class);

        return $store->conversationBelongsTo(
            $conversationId,
            Conversation::participantType($user),
            Conversation::participantKey($user),
        ) ? $conversationId : null;
    }
}
