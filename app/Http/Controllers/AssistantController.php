<?php

namespace App\Http\Controllers;

use App\Ai\Agents\FinanceAssistant;
use App\Http\Requests\AssistantStreamRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Streaming\Events\Error;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\ToolCall;
use Laravel\Ai\Streaming\Events\ToolResult;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AssistantController extends Controller
{
    /**
     * Maximum number of prior messages kept as conversation context.
     */
    private const HISTORY_LIMIT = 20;

    /**
     * Heartbeat interval, in seconds, used to keep the SSE connection alive.
     */
    private const HEARTBEAT_SECONDS = 15;

    /**
     * Show the assistant page.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Assistant');
    }

    /**
     * Stream the assistant response as Server-Sent Events.
     */
    public function stream(AssistantStreamRequest $request): StreamedResponse
    {
        set_time_limit(300);
        ini_set('max_execution_time', '300');
        ignore_user_abort(false);

        $user = $request->user();
        $validated = $request->validated();

        $sessionId = $validated['session_id'] ?? 'user-'.$user->id;

        // The OpenCode Go endpoint requires a stable per-conversation session header.
        config([
            'ai.providers.opencode.headers.x-opencode-session' => 'finance-assistant-'.$sessionId,
            'ai.providers.opencode.headers.User-Agent' => 'expense-tracker/1.0',
        ]);

        /** @var array<int, Message> $history */
        $history = Collection::make($validated['history'] ?? [])
            ->slice(-self::HISTORY_LIMIT)
            ->map(fn (array $message): Message => new Message($message['role'], $message['content']))
            ->values()
            ->all();

        $agent = new FinanceAssistant($user, $history);

        $stream = $agent->stream($validated['message'], timeout: 300);

        return response()->stream(function () use ($stream): void {
            $lastPing = time();

            $send = function (array $payload) use (&$lastPing): void {
                echo 'data: '.json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n";

                $this->flushStream();

                $lastPing = time();
            };

            $heartbeat = function () use (&$lastPing): void {
                if (time() - $lastPing < self::HEARTBEAT_SECONDS) {
                    return;
                }

                echo ": ping\n\n";

                $this->flushStream();

                $lastPing = time();
            };

            try {
                foreach ($stream as $event) {
                    if (connection_aborted()) {
                        return;
                    }

                    $heartbeat();

                    if ($event instanceof TextDelta) {
                        $send(['type' => 'text', 'delta' => $event->delta]);
                    } elseif ($event instanceof ToolCall) {
                        $send([
                            'type' => 'tool_call',
                            'id' => $event->toolCall->id,
                            'name' => $event->toolCall->name,
                            'arguments' => $event->toolCall->arguments,
                        ]);
                    } elseif ($event instanceof ToolResult) {
                        if ($event->preliminary) {
                            continue;
                        }

                        $decoded = is_string($event->toolResult->result)
                            ? json_decode($event->toolResult->result, true)
                            : null;

                        $ok = $event->successful && (($decoded['ok'] ?? true) !== false);

                        $summary = match (true) {
                            is_array($decoded) && isset($decoded['summary']) => (string) $decoded['summary'],
                            $event->error !== null => $event->error,
                            $event->successful => 'Done.',
                            default => 'The tool could not complete the request.',
                        };

                        $send([
                            'type' => 'tool_result',
                            'id' => $event->toolResult->id,
                            'name' => $event->toolResult->name,
                            'summary' => $summary,
                            'ok' => $ok,
                        ]);
                    } elseif ($event instanceof Error) {
                        $send([
                            'type' => 'error',
                            'message' => 'The assistant provider returned an error. Please try again.',
                        ]);
                    }
                }
            } catch (Throwable $exception) {
                report($exception);

                $send([
                    'type' => 'error',
                    'message' => 'Something went wrong while generating the response. Please try again.',
                ]);
            }

            $send(['type' => 'done']);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Flush the current output buffers so each SSE frame reaches the client immediately.
     */
    private function flushStream(): void
    {
        if (ob_get_level() > 0) {
            @ob_flush();
        }

        flush();
    }
}
