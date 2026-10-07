<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Models\Conversation;
use stdClass;

class AssistantConversationController extends Controller
{
    /**
     * List the authenticated user's assistant conversations, newest first.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversationsTable = $this->conversationsTable();
        $messagesTable = $this->messagesTable();

        $conversations = DB::table($conversationsTable)
            ->select(['id', 'title', 'updated_at'])
            ->selectSub(
                DB::table($messagesTable)
                    ->selectRaw('count(*)')
                    ->whereColumn('conversation_id', $conversationsTable.'.id'),
                'messages_count',
            )
            ->where('participant_type', Conversation::participantType($user))
            ->where('participant_id', Conversation::participantKey($user))
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        return response()->json([
            'conversations' => $conversations->map(fn (stdClass $conversation): array => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'updated_at' => $conversation->updated_at === null
                    ? null
                    : Carbon::parse($conversation->updated_at)->toIso8601String(),
                'messages_count' => (int) $conversation->messages_count,
            ])->values(),
        ]);
    }

    /**
     * Return the messages of one of the authenticated user's conversations.
     */
    public function show(Request $request, string $conversation): JsonResponse
    {
        $record = $this->ownedConversation($request->user(), $conversation);

        $messages = DB::table($this->messagesTable())
            ->where('conversation_id', $conversation)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'role', 'content', 'created_at']);

        return response()->json([
            'conversation' => [
                'id' => $record->id,
                'title' => $record->title,
            ],
            'messages' => $messages->map(fn (stdClass $message): array => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => (string) $message->content,
                'created_at' => $message->created_at === null
                    ? null
                    : Carbon::parse($message->created_at)->toIso8601String(),
            ])->values(),
        ]);
    }

    /**
     * Delete one of the authenticated user's conversations and its messages.
     */
    public function destroy(Request $request, string $conversation): JsonResponse
    {
        $record = $this->ownedConversation($request->user(), $conversation);

        DB::table($this->messagesTable())->where('conversation_id', $record->id)->delete();
        DB::table($this->conversationsTable())->where('id', $record->id)->delete();

        return response()->json(['deleted' => true]);
    }

    /**
     * Resolve a conversation owned by the given user or fail with a 404.
     */
    private function ownedConversation(User $user, string $id): stdClass
    {
        $conversation = DB::table($this->conversationsTable())
            ->where('id', $id)
            ->where('participant_type', Conversation::participantType($user))
            ->where('participant_id', Conversation::participantKey($user))
            ->first(['id', 'title']);

        if ($conversation === null) {
            throw (new ModelNotFoundException)->setModel(Conversation::class, [$id]);
        }

        return $conversation;
    }

    /**
     * Resolve the agent conversations table name.
     */
    private function conversationsTable(): string
    {
        return (string) config('ai.conversations.tables.conversations', 'agent_conversations');
    }

    /**
     * Resolve the agent conversation messages table name.
     */
    private function messagesTable(): string
    {
        return (string) config('ai.conversations.tables.messages', 'agent_conversation_messages');
    }
}
