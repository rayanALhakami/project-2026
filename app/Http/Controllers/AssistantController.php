<?php

namespace App\Http\Controllers;

use App\Ai\Agents\TouristGuide;
use App\Ai\GuestParticipant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        $startedAt = microtime(true);
        $hasImage = $request->hasFile('image');
        $ownedConversationId = null;

        try {
            $prompt = $validated['message'];
            /** @var array<int, Image> $attachments */
            $attachments = [];

            if ($request->hasFile('image')) {
                $attachments[] = Image::fromUpload($request->file('image'));
                $prompt = 'تعرّف على المعلم الظاهر في الصورة المرفقة واذكر اسمه ومعلوماته من قاعدة البيانات. سؤال المستخدم: '.$validated['message'];
            }

            $agent = new TouristGuide;
            $user = $request->user();
            $participant = $user instanceof User ? $user : $this->guestParticipant($request);
            $ownedConversationId = $this->ownedConversationId($validated['conversation_id'] ?? null, $participant);

            $cacheKey = $this->dedupeCacheKey(
                prompt: $validated['message'],
                conversationId: $ownedConversationId,
                participant: $participant,
                attachments: $attachments,
            );

            if ($cacheKey !== null) {
                $cachedPayload = Cache::get($cacheKey);

                if (is_array($cachedPayload) && $this->isValidAssistantPayload($cachedPayload)) {
                    return response()->json([
                        ...$cachedPayload,
                        'meta' => $this->responseMeta(cached: true, fallback: false, startedAt: $startedAt),
                    ]);
                }
            }

            $provider = $attachments === [] ? null : Lab::Gemini;

            $response = $agent
                ->continueOrStart(
                    $ownedConversationId,
                    as: $participant,
                )
                ->prompt($prompt, attachments: $attachments, provider: $provider);

            $payload = [
                'reply' => (string) $response,
                'conversation_id' => $response->conversationId,
                'user_message_id' => $response->userMessageId,
                'assistant_message_id' => $response->assistantMessageId,
            ];

            if ($cacheKey !== null) {
                Cache::put($cacheKey, $payload, now()->addMinutes(2));
            }

            return response()->json([
                ...$payload,
                'meta' => $this->responseMeta(cached: false, fallback: false, startedAt: $startedAt),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Assistant chat request failed; using fallback when possible.', [
                'exception' => $exception,
            ]);

            if (! $hasImage) {
                return response()->json([
                    'reply' => $this->fallbackReply($validated['message']),
                    'conversation_id' => $ownedConversationId,
                    'user_message_id' => null,
                    'assistant_message_id' => null,
                    'meta' => $this->responseMeta(cached: false, fallback: true, startedAt: $startedAt),
                ]);
            }

            return response()->json([
                'error' => 'عذراً، حدث خطأ أثناء معالجة طلبك. حاول مرة أخرى بعد قليل.',
                'meta' => $this->responseMeta(cached: false, fallback: true, startedAt: $startedAt),
            ], 503);
        }
    }

    /**
     * Build a short-lived dedupe cache key for duplicate requests.
     *
     * @param  array<int, Image>  $attachments
     */
    private function dedupeCacheKey(
        string $prompt,
        ?string $conversationId,
        User|GuestParticipant $participant,
        array $attachments,
    ): ?string {
        if ($attachments !== []) {
            return null;
        }

        $normalizedPrompt = $this->normalizePrompt($prompt);

        if ($normalizedPrompt === '') {
            return null;
        }

        $participantKey = $participant instanceof User
            ? 'user:'.$participant->getKey()
            : 'guest:'.$participant->id;

        return 'assistant:dedupe:'.sha1($participantKey.'|'.($conversationId ?? 'new').'|'.$normalizedPrompt);
    }

    /**
     * Normalize user text before dedupe checks.
     */
    private function normalizePrompt(string $prompt): string
    {
        $singleSpaced = preg_replace('/\s+/u', ' ', trim($prompt));

        if ($singleSpaced === null) {
            return trim($prompt);
        }

        return mb_strtolower($singleSpaced);
    }

    /**
     * Ensure cached payload shape is safe to return.
     *
     * @param  array<mixed>  $payload
     */
    private function isValidAssistantPayload(array $payload): bool
    {
        $conversationId = $payload['conversation_id'] ?? null;

        return is_string($payload['reply'] ?? null)
            && ($conversationId === null || is_string($conversationId));
    }

    /**
     * Build API metadata consumed by the chat UI.
     *
     * @return array{cached: bool, fallback: bool, response_ms: int}
     */
    private function responseMeta(bool $cached, bool $fallback, float $startedAt): array
    {
        return [
            'cached' => $cached,
            'fallback' => $fallback,
            'response_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ];
    }

    /**
     * Return a concise fallback reply when providers are temporarily unavailable.
     */
    private function fallbackReply(string $message): string
    {
        if (preg_match('/\p{Arabic}/u', $message) === 1) {
            return "الخدمة الذكية عليها ضغط الآن، وهذا رد سريع مؤقت.\n\n- أعد المحاولة بعد ثوانٍ لرد كامل.\n- أو أرسل: المدينة + عدد الأيام + الميزانية لأبني لك خطة مختصرة فوراً.";
        }

        return "The assistant is under high load right now, so this is a quick temporary reply.\n\n- Retry in a few seconds for a full answer.\n- Or send: city + days + budget and I will build a short instant plan.";
    }

    /**
     * Only continue a conversation owned by the given participant; otherwise start a new one.
     */
    private function ownedConversationId(?string $conversationId, User|GuestParticipant $participant): ?string
    {
        if ($conversationId === null) {
            return null;
        }

        $store = resolve(ConversationStore::class);

        return $store->conversationBelongsTo(
            $conversationId,
            Conversation::participantType($participant),
            Conversation::participantKey($participant),
        ) ? $conversationId : null;
    }

    /**
     * Resolve the session-scoped participant that remembers a guest's conversations.
     */
    private function guestParticipant(Request $request): GuestParticipant
    {
        $session = $request->session();

        $id = $session->get('ai.guest_participant_id');

        if (! is_int($id)) {
            $id = random_int(1, PHP_INT_MAX);
            $session->put('ai.guest_participant_id', $id);
        }

        return new GuestParticipant($id);
    }
}
