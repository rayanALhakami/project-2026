<script module lang="ts">
    import { assistant } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'المساعد',
                href: assistant(),
            },
        ],
    };
</script>

<script lang="ts">
    import ArrowUp from '@lucide/svelte/icons/arrow-up';
    import RotateCcw from '@lucide/svelte/icons/rotate-ccw';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Square from '@lucide/svelte/icons/square';
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import ChatMessage from '@/components/assistant/ChatMessage.svelte';
    import { streamAssistant } from '@/lib/assistant';
    import type { AssistantFrame, ChatMessage as ChatMessageType } from '@/types';

    const EXAMPLES = [
        'كم صرفت هذا الشهر؟',
        'أضف مصروف ٥٠ ريال قهوة أمس',
        'اعرض أكبر ٥ مصروفات',
        'احذف آخر عملية',
    ];

    const STREAM_TIMEOUT_MS = 300_000;

    let messages = $state<ChatMessageType[]>([]);
    let input = $state('');
    let isStreaming = $state(false);
    let sessionId = $state('');
    let composerEl = $state<HTMLTextAreaElement | undefined>(undefined);
    let scrollEl = $state<HTMLDivElement | undefined>(undefined);

    let controller: AbortController | null = null;
    let activeAssistantId = $state<string | null>(null);
    let buffer = '';
    let flushTimer: ReturnType<typeof setTimeout> | undefined;
    let timedOut = false;

    const scrollKey = $derived(
        messages
            .map((message) => `${message.id}:${message.content.length}:${message.toolCalls.length}`)
            .join('|'),
    );

    $effect(() => {
        void scrollKey;

        if (scrollEl) {
            scrollEl.scrollTop = scrollEl.scrollHeight;
        }
    });

    onMount(() => {
        if (sessionId === '') {
            sessionId = crypto.randomUUID();
        }
    });

    function ensureSession(): void {
        if (sessionId === '') {
            sessionId = crypto.randomUUID();
        }
    }

    function updateAssistant(id: string, mutate: (message: ChatMessageType) => void): void {
        const target = messages.find((message) => message.id === id);

        if (target) {
            mutate(target);
        }
    }

    function applyBuffer(): void {
        if (activeAssistantId === null) {
            return;
        }

        const value = buffer;
        updateAssistant(activeAssistantId, (message) => {
            message.content = value;
        });
    }

    function scheduleFlush(): void {
        if (flushTimer !== undefined) {
            return;
        }

        flushTimer = setTimeout(() => {
            flushTimer = undefined;
            applyBuffer();
        }, 60);
    }

    function flushNow(): void {
        if (flushTimer !== undefined) {
            clearTimeout(flushTimer);
            flushTimer = undefined;
        }

        applyBuffer();
    }

    function handleFrame(frame: AssistantFrame): void {
        if (activeAssistantId === null) {
            return;
        }

        const id = activeAssistantId;

        switch (frame.type) {
            case 'text':
                buffer += frame.delta;
                scheduleFlush();
                break;

            case 'tool_call':
                flushNow();
                updateAssistant(id, (message) => {
                    message.toolCalls = [
                        ...message.toolCalls,
                        {
                            id: frame.id,
                            name: frame.name,
                            arguments: frame.arguments ?? {},
                            status: 'running',
                        },
                    ];
                });
                break;

            case 'tool_result':
                updateAssistant(id, (message) => {
                    const call = message.toolCalls.find((item) => item.id === frame.id);

                    if (call) {
                        call.status = frame.ok ? 'success' : 'error';
                        call.summary = frame.summary;
                    }
                });
                break;

            case 'error':
                updateAssistant(id, (message) => {
                    message.error = frame.message;
                });
                break;

            case 'done':
                flushNow();
                break;
        }
    }

    async function send(text: string): Promise<void> {
        const trimmed = text.trim();

        if (trimmed === '' || isStreaming) {
            return;
        }

        ensureSession();

        const history = messages
            .filter((message) => message.content.trim() !== '' && !message.error)
            .map((message) => ({ role: message.role, content: message.content }));

        const userMessage: ChatMessageType = {
            id: crypto.randomUUID(),
            role: 'user',
            content: trimmed,
            toolCalls: [],
        };

        const assistantMessage: ChatMessageType = {
            id: crypto.randomUUID(),
            role: 'assistant',
            content: '',
            toolCalls: [],
        };

        messages = [...messages, userMessage, assistantMessage];
        input = '';
        buffer = '';
        activeAssistantId = assistantMessage.id;
        isStreaming = true;
        timedOut = false;

        controller = new AbortController();

        const timeout = setTimeout(() => {
            timedOut = true;
            controller?.abort();
        }, STREAM_TIMEOUT_MS);

        try {
            await streamAssistant(
                { message: trimmed, history, session_id: sessionId },
                handleFrame,
                controller.signal,
            );
        } catch (error) {
            if (timedOut) {
                updateAssistant(assistantMessage.id, (message) => {
                    message.error = 'انتهت مهلة الاتصال. جرّب إرسال الرسالة مرة أخرى.';
                });
            } else if ((error as Error)?.name !== 'AbortError') {
                updateAssistant(assistantMessage.id, (message) => {
                    message.error = 'تعذّر الاتصال بالمساعد. تحقّق من الشبكة وحاول مجدداً.';
                });
            }
        } finally {
            clearTimeout(timeout);
            flushNow();
            isStreaming = false;
            controller = null;
            activeAssistantId = null;
            composerEl?.focus();
        }
    }

    function stop(): void {
        controller?.abort();
    }

    function newChat(): void {
        controller?.abort();
        messages = [];
        input = '';
        buffer = '';
        activeAssistantId = null;
        sessionId = crypto.randomUUID();
    }

    function retry(assistantId: string): void {
        const index = messages.findIndex((message) => message.id === assistantId);
        const previous = index > 0 ? messages[index - 1] : undefined;

        if (previous === undefined || previous.role !== 'user') {
            return;
        }

        const text = previous.content;
        messages = messages.slice(0, index - 1);
        void send(text);
    }

    function onKeydown(event: KeyboardEvent): void {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            void send(input);
        }
    }
</script>

<AppHead title="المساعد" />

<div class="mx-auto flex min-h-0 w-full max-w-3xl flex-1 flex-col gap-4 p-4 md:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                المساعد المالي
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                اسأل عن مصاريفك أو اطلب إضافتها وتعديلها عبر المحادثة
            </p>
        </div>
        <button
            type="button"
            onclick={newChat}
            disabled={messages.length === 0 && !isStreaming}
            class="inline-flex items-center gap-2 rounded-full border border-border px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted disabled:opacity-40"
        >
            <RotateCcw class="size-4" />
            محادثة جديدة
        </button>
    </div>

    <div
        class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-[18px] border border-black/[0.06] bg-secondary/40"
    >
        <div
            bind:this={scrollEl}
            role="log"
            aria-live="polite"
            aria-label="المحادثة"
            class="flex min-h-0 flex-1 flex-col gap-4 overflow-y-auto p-4"
        >
            {#if messages.length === 0}
                <div class="flex flex-1 flex-col items-center justify-center gap-4 py-10 text-center">
                    <span
                        class="flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary"
                    >
                        <Sparkles class="size-6" />
                    </span>
                    <div>
                        <p class="text-base font-semibold text-foreground">
                            كيف أساعدك في مصاريفك؟
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            جرّب أحد الأمثلة التالية
                        </p>
                    </div>
                    <div class="flex flex-wrap justify-center gap-2">
                        {#each EXAMPLES as example (example)}
                            <button
                                type="button"
                                onclick={() => send(example)}
                                class="rounded-full border border-border bg-card px-4 py-2 text-sm text-foreground transition hover:border-primary hover:text-primary"
                            >
                                {example}
                            </button>
                        {/each}
                    </div>
                </div>
            {:else}
                {#each messages as message (message.id)}
                    <ChatMessage
                        {message}
                        streaming={isStreaming && message.id === activeAssistantId}
                    />
                {/each}
                {#if messages.some((message) => message.error)}
                    <button
                        type="button"
                        onclick={() => {
                            const failed = messages.find((message) => message.error);

                            if (failed) {
                                retry(failed.id);
                            }
                        }}
                        class="self-center rounded-full border border-border bg-card px-4 py-2 text-sm text-foreground transition hover:bg-muted"
                    >
                        إعادة المحاولة
                    </button>
                {/if}
            {/if}
        </div>

        <div class="border-t border-black/[0.06] bg-card p-3">
            <div class="flex items-end gap-2">
                <textarea
                    bind:this={composerEl}
                    bind:value={input}
                    onkeydown={onKeydown}
                    disabled={isStreaming}
                    rows="1"
                    placeholder="اكتب رسالتك... (Enter للإرسال، Shift+Enter لسطر جديد)"
                    aria-label="رسالتك"
                    class="max-h-40 min-h-11 flex-1 resize-none rounded-2xl border border-border bg-background px-4 py-3 text-[15px] text-foreground outline-none focus:border-primary disabled:opacity-60"
                ></textarea>

                {#if isStreaming}
                    <button
                        type="button"
                        onclick={stop}
                        aria-label="إيقاف"
                        class="flex size-11 shrink-0 items-center justify-center rounded-full bg-destructive text-destructive-foreground transition active:scale-95"
                    >
                        <Square class="size-4" />
                    </button>
                {:else}
                    <button
                        type="button"
                        onclick={() => send(input)}
                        disabled={input.trim() === ''}
                        aria-label="إرسال"
                        class="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground transition active:scale-95 disabled:opacity-40"
                    >
                        <ArrowUp class="size-5" />
                    </button>
                {/if}
            </div>
        </div>
    </div>
</div>
