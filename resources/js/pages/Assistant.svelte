<script lang="ts">
    import { page, useHttp } from '@inertiajs/svelte';
    import History from '@lucide/svelte/icons/history';
    import ImageIcon from '@lucide/svelte/icons/image';
    import LoaderCircle from '@lucide/svelte/icons/loader-circle';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Mic from '@lucide/svelte/icons/mic';
    import Mountain from '@lucide/svelte/icons/mountain';
    import Plus from '@lucide/svelte/icons/plus';
    import Route from '@lucide/svelte/icons/route';
    import Send from '@lucide/svelte/icons/send';
    import Settings2 from '@lucide/svelte/icons/settings-2';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Square from '@lucide/svelte/icons/square';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Utensils from '@lucide/svelte/icons/utensils';
    import Volume2 from '@lucide/svelte/icons/volume-2';
    import VolumeX from '@lucide/svelte/icons/volume-x';
    import X from '@lucide/svelte/icons/x';
    import { onDestroy, onMount } from 'svelte';
    import { toast } from 'svelte-sonner';
    import AppHead from '@/components/AppHead.svelte';
    import SiteHeader from '@/components/SiteHeader.svelte';
    import {
        Popover,
        PopoverContent,
        PopoverTrigger,
    } from '@/components/ui/popover';
    import { Toaster } from '@/components/ui/sonner';
    import { Switch } from '@/components/ui/switch';
    import { getLocale, t } from '@/lib/i18n.svelte';
    import {
        cancelRecording,
        isRecordingSupported,
        setVoiceAutoPlay,
        setVoiceGender,
        setVoiceLanguage,
        speak,
        startRecording,
        stopRecording,
        stopSpeaking,
        voice,
        voiceLanguageOptions,
    } from '@/lib/voice.svelte';
    import { chat } from '@/routes/assistant';
    import {
        destroy as conversationDestroy,
        index as conversationsIndex,
        show as conversationShow,
    } from '@/routes/assistant/conversations';

    interface ChatMessage {
        id: number;
        role: 'user' | 'assistant' | 'error';
        text: string;
        imageUrl?: string;
    }

    interface ChatResponse {
        reply: string;
        conversation_id: string | null;
        user_message_id: number | string | null;
        assistant_message_id: number | string | null;
    }

    interface ConversationSummary {
        id: string;
        title: string;
        updated_at: string | null;
        messages_count: number;
    }

    interface ConversationMessagePayload {
        id: string;
        role: string;
        content: string;
        created_at: string | null;
    }

    const MAX_IMAGE_BYTES = 8 * 1024 * 1024;
    const UUID_PATTERN =
        /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;

    const suggestionSets = {
        ar: [
            'خطط لي رحلة ٣ أيام في العلا',
            'أفضل أماكن القهوة في الرياض؟',
            'قارن بين جدة وأبها',
            'وش الطقس في أبها؟',
        ],
        en: [
            'Plan a 3-day trip to AlUla',
            'Best coffee spots in Riyadh?',
            'Compare Jeddah and Abha',
            'What is the weather in Abha?',
        ],
    };

    const categorySets = {
        ar: {
            places: 'أبي معالم سياحية',
            restaurants: 'أبي مطاعم',
            activities: 'أبي أنشطة',
            plan: 'أبي خطة رحلة',
            advice: 'وش تنصحني؟',
        },
        en: {
            places: 'I want tourist landmarks',
            restaurants: 'I want restaurants',
            activities: 'I want activities',
            plan: 'I want a trip plan',
            advice: 'What do you recommend?',
        },
    };

    const authUser = $derived(page.props.auth.user);

    let nextId = 2;
    let messages = $state<ChatMessage[]>([
        {
            id: 1,
            role: 'assistant',
            text: t('assistant.prompt'),
        },
    ]);
    let conversationId = $state<string | null>(null);
    let conversations = $state<ConversationSummary[]>([]);
    let historyOpen = $state(false);
    let historyLoading = $state(false);
    let conversationLoadingId = $state<string | null>(null);
    let deletingConversationId = $state<string | null>(null);
    let imagePreview = $state<string | null>(null);
    let speakingId = $state<number | null>(null);
    let container: HTMLDivElement | null = null;
    let fileInput: HTMLInputElement | null = null;

    const objectUrls = new Set<string>();

    const http = useHttp<{ message: string; image: File | null }, ChatResponse>({
        message: '',
        image: null,
    });

    http.transform((data) => {
        if (isValidConversationId(conversationId)) {
            return { ...data, conversation_id: conversationId };
        }

        return data;
    });

    const conversationsHttp = useHttp<
        Record<string, never>,
        { conversations: ConversationSummary[] }
    >({});
    const conversationHttp = useHttp<
        Record<string, never>,
        { messages: ConversationMessagePayload[] }
    >({});
    const conversationDeleteHttp = useHttp<
        Record<string, never>,
        { deleted: boolean }
    >({});

    const recordingSupported = isRecordingSupported();

    const isArabic = $derived(getLocale() === 'ar');
    const suggestions = $derived(isArabic ? suggestionSets.ar : suggestionSets.en);
    const prompts = $derived(isArabic ? categorySets.ar : categorySets.en);

    const categories = $derived([
        { key: 'places', label: t('assistant.catPlaces'), icon: MapPin, prompt: prompts.places },
        {
            key: 'restaurants',
            label: t('assistant.catRestaurants'),
            icon: Utensils,
            prompt: prompts.restaurants,
        },
        {
            key: 'activities',
            label: t('assistant.catActivities'),
            icon: Mountain,
            prompt: prompts.activities,
        },
        {
            key: 'plan',
            label: t('assistant.catPlan'),
            icon: Route,
            prompt: prompts.plan,
        },
        {
            key: 'advice',
            label: t('assistant.catAdvice'),
            icon: Sparkles,
            prompt: prompts.advice,
        },
    ]);

    $effect(() => {
        const count = messages.length;
        const isProcessing = http.processing;

        if (container && (count > 0 || isProcessing)) {
            container.scrollTop = container.scrollHeight;
        }
    });

    onMount(() => {
        if (authUser) {
            loadConversations();
        }

        const key = conversationKey();

        if (key === null || typeof window === 'undefined') {
            return;
        }

        const stored = window.localStorage.getItem(key);

        if (isValidConversationId(stored)) {
            conversationId = stored;
        }
    });

    onDestroy(() => {
        stopSpeaking();
        cancelRecording();
        objectUrls.forEach((url) => URL.revokeObjectURL(url));
        objectUrls.clear();
    });

    function isValidConversationId(value: unknown): value is string {
        return typeof value === 'string' && UUID_PATTERN.test(value);
    }

    function conversationKey(): string | null {
        const user = authUser;

        if (!user) {
            return null;
        }

        return `assistant-conversation-${user.id}`;
    }

    function persistConversation(id: string): void {
        const key = conversationKey();

        if (key === null || typeof window === 'undefined') {
            return;
        }

        window.localStorage.setItem(key, id);
    }

    function loadConversations(): void {
        if (!authUser) {
            return;
        }

        historyLoading = true;

        conversationsHttp
            .get(conversationsIndex.url(), {
                onSuccess: (response) => {
                    conversations = response.conversations ?? [];
                },
                onFinish: () => {
                    historyLoading = false;
                },
            })
            .catch(() => {
                historyLoading = false;
            });
    }

    function startNewChat(): void {
        conversationId = null;

        const key = conversationKey();

        if (key !== null && typeof window !== 'undefined') {
            window.localStorage.removeItem(key);
        }

        stopSpeaking();
        speakingId = null;
        nextId = 2;
        messages = [{ id: 1, role: 'assistant', text: t('assistant.prompt') }];
        historyOpen = false;
    }

    function openConversation(id: string): void {
        if (conversationLoadingId !== null) {
            return;
        }

        conversationLoadingId = id;
        stopSpeaking();
        speakingId = null;

        conversationHttp
            .get(conversationShow.url(id), {
                onSuccess: (response) => {
                    const loaded = (response.messages ?? [])
                        .filter(
                            (message) =>
                                message.role === 'user' ||
                                message.role === 'assistant',
                        )
                        .filter((message) => message.content.trim() !== '')
                        .map(
                            (message): ChatMessage => ({
                                id: nextId++,
                                role: message.role as 'user' | 'assistant',
                                text: message.content,
                            }),
                        );

                    messages =
                        loaded.length > 0
                            ? loaded
                            : [
                                  {
                                      id: nextId++,
                                      role: 'assistant',
                                      text: t('assistant.prompt'),
                                  },
                              ];
                    conversationId = id;
                    persistConversation(id);
                    historyOpen = false;
                },
                onHttpException: () => {
                    toast.error(failureText());
                },
                onNetworkError: () => {
                    toast.error(failureText());
                },
                onFinish: () => {
                    conversationLoadingId = null;
                },
            })
            .catch(() => {
                conversationLoadingId = null;
            });
    }

    function deleteConversation(id: string): void {
        if (deletingConversationId !== null) {
            return;
        }

        if (!window.confirm(t('assistant.deleteConfirm'))) {
            return;
        }

        deletingConversationId = id;

        conversationDeleteHttp
            .delete(conversationDestroy.url(id), {
                onSuccess: () => {
                    conversations = conversations.filter(
                        (conversation) => conversation.id !== id,
                    );

                    if (conversationId === id) {
                        startNewChat();
                    }
                },
                onHttpException: () => {
                    toast.error(failureText());
                },
                onNetworkError: () => {
                    toast.error(failureText());
                },
                onFinish: () => {
                    deletingConversationId = null;
                },
            })
            .catch(() => {
                deletingConversationId = null;
            });
    }

    function formatConversationDate(value: string | null): string {
        if (value === null) {
            return '';
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return '';
        }

        return new Intl.DateTimeFormat(getLocale(), {
            day: 'numeric',
            month: 'short',
        }).format(date);
    }

    function failureText(): string {
        return isArabic
            ? 'عذراً، حدث خطأ أثناء معالجة طلبك. حاول مرة أخرى بعد قليل.'
            : 'Sorry, something went wrong while processing your request. Please try again shortly.';
    }

    function parseErrorResponse(data: string): string {
        try {
            const payload = JSON.parse(data) as { error?: unknown };

            if (typeof payload.error === 'string' && payload.error !== '') {
                return payload.error;
            }
        } catch {
            // Fall back to the generic message below.
        }

        return failureText();
    }

    function appendError(text: string): void {
        messages = [...messages, { id: nextId++, role: 'error', text }];
    }

    function send(text?: string, fromVoice = false): void {
        if (http.processing) {
            return;
        }

        if (text !== undefined) {
            http.message = text;
        }

        const value = http.message.trim();

        if (value === '') {
            return;
        }

        stopSpeaking();
        http.clearErrors();

        const wasNewConversation = !isValidConversationId(conversationId);

        http.post(chat.url(), {
            onSuccess: (response) => {
                const reply =
                    typeof response?.reply === 'string' ? response.reply : '';

                if (reply === '') {
                    appendError(failureText());

                    return;
                }

                const imageUrl = imagePreview ?? undefined;
                const userMessage: ChatMessage = {
                    id: nextId++,
                    role: 'user',
                    text: value,
                    imageUrl,
                };
                const assistantMessage: ChatMessage = {
                    id: nextId++,
                    role: 'assistant',
                    text: reply,
                };

                messages = [...messages, userMessage, assistantMessage];

                if (isValidConversationId(response.conversation_id)) {
                    conversationId = response.conversation_id;
                    persistConversation(response.conversation_id);

                    if (wasNewConversation) {
                        loadConversations();
                    }
                }

                http.message = '';
                http.image = null;
                imagePreview = null;

                if (fileInput) {
                    fileInput.value = '';
                }

                if (fromVoice && voice.autoPlay) {
                    speakingId = assistantMessage.id;
                    void speak(reply);
                }
            },
            onHttpException: (response) => {
                appendError(parseErrorResponse(response.data));
            },
            onNetworkError: () => {
                appendError(failureText());
            },
        }).catch(() => {
            // Failures are surfaced through the callbacks above.
        });
    }

    function createPreview(file: File): string {
        const url = URL.createObjectURL(file);
        objectUrls.add(url);

        return url;
    }

    function removeImage(): void {
        if (imagePreview !== null) {
            URL.revokeObjectURL(imagePreview);
            objectUrls.delete(imagePreview);
        }

        imagePreview = null;
        http.image = null;

        if (fileInput) {
            fileInput.value = '';
        }
    }

    function onImageSelected(event: Event): void {
        const target = event.currentTarget as HTMLInputElement;
        const file = target.files?.[0] ?? null;

        if (file === null) {
            return;
        }

        if (file.size > MAX_IMAGE_BYTES) {
            toast.error(
                isArabic
                    ? 'حجم الصورة يجب ألا يتجاوز ٨ ميجابايت.'
                    : 'The image must be 8MB or smaller.',
            );
            target.value = '';

            return;
        }

        removeImage();
        http.image = file;
        imagePreview = createPreview(file);
    }

    async function toggleMic(): Promise<void> {
        if (voice.processing) {
            return;
        }

        if (voice.recording) {
            const text = await stopRecording();

            if (text === null) {
                if (voice.error) {
                    toast.error(voice.error);
                }

                return;
            }

            const value = text.trim();

            if (value === '') {
                toast.error(
                    voice.error ??
                        'لم يتم التعرف على أي كلام. حاول مرة أخرى.',
                );

                return;
            }

            http.message = value;
            send(value, true);

            return;
        }

        if (!recordingSupported) {
            toast.error(
                'متصفحك لا يدعم تسجيل الصوت. جرّب متصفحاً حديثاً مثل كروم أو إيدج.',
            );

            return;
        }

        stopSpeaking();

        try {
            await startRecording();
        } catch (error) {
            toast.error(
                error instanceof Error
                    ? error.message
                    : 'تعذر بدء التسجيل الصوتي.',
            );
        }
    }

    async function toggleSpeak(message: ChatMessage): Promise<void> {
        if (voice.speaking && speakingId === message.id) {
            stopSpeaking();
            speakingId = null;

            return;
        }

        speakingId = message.id;
        await speak(message.text);

        if (!voice.speaking) {
            speakingId = null;
        }
    }
</script>

<AppHead title={t('assistant.title')} />

<SiteHeader active="assistant" />

<div
    class="flex h-dvh flex-col overflow-hidden bg-background pt-16 pb-[calc(4rem_+_env(safe-area-inset-bottom))] md:pb-0"
>
    <div
        class="mx-auto flex min-h-0 w-full max-w-4xl flex-1 flex-col px-4 py-4 md:px-6"
    >
        <div class="flex items-center justify-between gap-3 pb-4">
            <div class="flex min-w-0 items-center gap-3">
                <span
                    class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-[#0b1e33] text-emerald-300"
                >
                    <Sparkles class="size-6" />
                </span>
                <div class="min-w-0">
                    <h1
                        class="truncate text-xl font-bold text-foreground sm:text-2xl"
                    >
                        {t('assistant.title')}
                    </h1>
                    <p class="truncate text-sm text-muted-foreground">
                        {t('assistant.subtitle')}
                    </p>
                </div>
            </div>

            {#if authUser}
                <Popover
                    bind:open={historyOpen}
                    onOpenChange={(open) => {
                        if (open) {
                            loadConversations();
                        }
                    }}
                >
                    <PopoverTrigger
                        class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-card text-secondary-foreground ring-1 ring-border transition hover:text-emerald-700 hover:ring-emerald-300 dark:hover:text-emerald-300"
                        aria-label={t('assistant.history')}
                        title={t('assistant.history')}
                    >
                        <History class="size-5" />
                    </PopoverTrigger>
                    <PopoverContent align="end" class="w-80 gap-0 p-2">
                        <div
                            class="flex items-center justify-between gap-2 px-2 pb-2"
                        >
                            <span class="text-sm font-bold text-foreground">
                                {t('assistant.history')}
                            </span>
                            <button
                                type="button"
                                onclick={startNewChat}
                                class="inline-flex min-h-8 items-center gap-1 rounded-full px-2.5 text-xs font-bold text-emerald-700 transition hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-emerald-950/40"
                            >
                                <Plus class="size-3.5" />
                                {t('assistant.newChat')}
                            </button>
                        </div>

                        <div class="max-h-80 overflow-y-auto">
                            {#if historyLoading}
                                <div class="flex flex-col gap-2 p-2">
                                    {#each [1, 2, 3] as row (row)}
                                        <div
                                            class="h-12 animate-pulse rounded-xl bg-muted"
                                        ></div>
                                    {/each}
                                </div>
                            {:else if conversations.length === 0}
                                <p
                                    class="px-3 py-6 text-center text-sm text-muted-foreground"
                                >
                                    {t('assistant.emptyHistory')}
                                </p>
                            {:else}
                                {#each conversations as conversation (conversation.id)}
                                    <div class="flex items-center gap-1">
                                        <button
                                            type="button"
                                            onclick={() =>
                                                openConversation(
                                                    conversation.id,
                                                )}
                                            class="flex min-h-12 min-w-0 flex-1 flex-col items-start justify-center rounded-xl px-3 py-1.5 text-start transition hover:bg-muted {conversationId ===
                                            conversation.id
                                                ? 'bg-muted'
                                                : ''}"
                                        >
                                            <span
                                                class="w-full truncate text-sm font-bold text-foreground"
                                            >
                                                {conversation.title}
                                            </span>
                                            {#if conversationLoadingId === conversation.id}
                                                <LoaderCircle
                                                    class="size-4 animate-spin text-muted-foreground"
                                                />
                                            {:else}
                                                <span
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    {formatConversationDate(
                                                        conversation.updated_at,
                                                    )}
                                                </span>
                                            {/if}
                                        </button>
                                        <button
                                            type="button"
                                            onclick={() =>
                                                deleteConversation(
                                                    conversation.id,
                                                )}
                                            disabled={deletingConversationId ===
                                                conversation.id}
                                            class="flex size-9 shrink-0 items-center justify-center rounded-xl text-muted-foreground/60 transition hover:text-red-600 disabled:opacity-40"
                                            aria-label={t(
                                                'assistant.deleteChat',
                                            )}
                                            title={t('assistant.deleteChat')}
                                        >
                                            {#if deletingConversationId === conversation.id}
                                                <LoaderCircle
                                                    class="size-4 animate-spin"
                                                />
                                            {:else}
                                                <Trash2 class="size-4" />
                                            {/if}
                                        </button>
                                    </div>
                                {/each}
                            {/if}
                        </div>
                    </PopoverContent>
                </Popover>
            {/if}
        </div>

        <div
            class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-[24px] bg-card shadow-sm ring-1 ring-border"
        >
            <div
                bind:this={container}
                class="flex min-h-0 flex-1 flex-col gap-4 overflow-y-auto p-4 sm:p-5"
            >
                {#each messages as message (message.id)}
                    <div
                        class="flex gap-3 {message.role === 'user'
                            ? 'flex-row-reverse'
                            : ''}"
                    >
                        <span
                            class="flex size-9 shrink-0 items-center justify-center rounded-full text-base {message.role ===
                            'user'
                                ? 'bg-[#0b1e33] text-white'
                                : message.role === 'error'
                                  ? 'bg-red-50 text-red-600 ring-1 ring-red-100 dark:bg-red-950/50 dark:text-red-300 dark:ring-red-900/60'
                                  : 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-100 dark:ring-emerald-900/60'}"
                            aria-hidden="true"
                        >
                            {message.role === 'user'
                                ? '👤'
                                : message.role === 'error'
                                  ? '⚠️'
                                  : '✨'}
                        </span>
                        {#if message.role === 'error'}
                            <div
                                class="max-w-[92%] rounded-[18px] bg-red-50 px-4 py-3 text-base leading-relaxed text-red-700 ring-1 ring-red-100 sm:max-w-[85%] dark:bg-red-950/40 dark:text-red-300 dark:ring-red-900/60"
                            >
                                <p class="whitespace-pre-wrap">{message.text}</p>
                            </div>
                        {:else}
                            <div
                                class="rounded-[18px] px-4 py-3 text-base leading-relaxed {message.role ===
                                'user'
                                    ? 'max-w-[85%] bg-[#0b1e33] text-white'
                                    : 'flex max-w-[92%] flex-col items-start gap-1 bg-muted text-foreground sm:max-w-[85%]'}"
                            >
                                {#if message.role === 'user'}
                                    <div class="flex flex-col gap-2">
                                        {#if message.imageUrl}
                                            <img
                                                src={message.imageUrl}
                                                alt=""
                                                class="max-h-48 max-w-full rounded-xl object-cover"
                                            />
                                        {/if}
                                        <p class="whitespace-pre-wrap">
                                            {message.text}
                                        </p>
                                    </div>
                                {:else}
                                    <p class="whitespace-pre-wrap">
                                        {message.text}
                                    </p>
                                    <button
                                        type="button"
                                        onclick={() => toggleSpeak(message)}
                                        class="mt-1 inline-flex min-h-8 items-center gap-1.5 rounded-full px-2 text-xs font-bold transition {voice.speaking &&
                                        speakingId === message.id
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                                            : 'text-muted-foreground hover:bg-background hover:text-foreground'}"
                                        aria-label={voice.speaking &&
                                        speakingId === message.id
                                            ? t('voice.stop')
                                            : t('voice.play')}
                                        title={voice.speaking &&
                                        speakingId === message.id
                                            ? t('voice.stop')
                                            : t('voice.play')}
                                    >
                                        {#if voice.speaking && speakingId === message.id}
                                            <Square class="size-3.5" />
                                            {t('voice.stop')}
                                        {:else}
                                            <Volume2 class="size-4" />
                                            {t('voice.play')}
                                        {/if}
                                    </button>
                                {/if}
                            </div>
                        {/if}
                    </div>
                {/each}

                {#if http.processing}
                    <div class="flex gap-3">
                        <span
                            class="flex size-9 shrink-0 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-base text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            aria-hidden="true">✨</span
                        >
                        <div
                            class="flex items-center gap-1 rounded-[18px] bg-muted px-4 py-3"
                        >
                            <span
                                class="size-2 animate-bounce rounded-full bg-emerald-400 [animation-delay:-0.3s]"
                            ></span>
                            <span
                                class="size-2 animate-bounce rounded-full bg-emerald-400 [animation-delay:-0.15s]"
                            ></span>
                            <span
                                class="size-2 animate-bounce rounded-full bg-emerald-400"
                            ></span>
                        </div>
                    </div>
                {/if}

                {#if messages.length <= 1}
                    <div class="flex flex-col gap-3 pt-2">
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            {#each categories as category (category.key)}
                                <button
                                    type="button"
                                    onclick={() => send(category.prompt)}
                                    class="flex min-h-14 items-center gap-2 rounded-xl bg-card px-4 text-sm font-bold text-secondary-foreground ring-1 ring-border transition hover:ring-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300"
                                >
                                    <category.icon class="size-5 shrink-0" />
                                    <span class="truncate">{category.label}</span>
                                </button>
                            {/each}
                        </div>

                        <div class="flex flex-col gap-2">
                            <p class="text-xs font-bold text-muted-foreground">
                                {t('assistant.tryAsking')}
                            </p>
                            <div class="flex flex-wrap gap-2">
                                {#each suggestions as suggestion (suggestion)}
                                    <button
                                        type="button"
                                        onclick={() => send(suggestion)}
                                        class="inline-flex min-h-10 items-center rounded-full bg-muted/60 px-4 text-sm text-muted-foreground ring-1 ring-border transition hover:text-emerald-700 hover:ring-emerald-300 dark:hover:text-emerald-300"
                                    >
                                        {suggestion}
                                    </button>
                                {/each}
                            </div>
                        </div>
                    </div>
                {/if}
            </div>

            <form
                onsubmit={(event) => {
                    event.preventDefault();
                    send();
                }}
                class="flex flex-col border-t border-border bg-card"
            >
                {#if imagePreview}
                    <div class="flex items-center gap-2 px-3 pt-3">
                        <div class="relative">
                            <img
                                src={imagePreview}
                                alt=""
                                class="size-16 rounded-xl object-cover ring-1 ring-border"
                            />
                            <button
                                type="button"
                                onclick={removeImage}
                                class="absolute -end-1.5 -top-1.5 flex size-5 items-center justify-center rounded-full bg-[#0b1e33] text-white shadow-sm transition hover:brightness-110"
                                aria-label={isArabic
                                    ? 'إزالة الصورة'
                                    : 'Remove image'}
                                title={isArabic ? 'إزالة الصورة' : 'Remove image'}
                            >
                                <X class="size-3" />
                            </button>
                        </div>
                    </div>
                {/if}

                {#if http.errors.message}
                    <p class="px-4 pt-3 text-sm text-red-600 dark:text-red-400">
                        {http.errors.message}
                    </p>
                {/if}

                <div class="flex items-end gap-2 p-3">
                    <input
                        bind:this={fileInput}
                        type="file"
                        accept="image/*"
                        class="hidden"
                        onchange={onImageSelected}
                    />
                    <div
                        class="flex min-w-0 flex-1 flex-col rounded-2xl bg-muted/60 ring-1 ring-border transition focus-within:ring-2 focus-within:ring-emerald-400"
                    >
                        <input
                            bind:value={http.message}
                            type="text"
                            placeholder={t('assistant.placeholder')}
                            aria-label={t('assistant.placeholder')}
                            class="w-full bg-transparent px-4 pt-3.5 pb-1.5 text-base text-foreground outline-none placeholder:text-muted-foreground"
                        />
                        <div class="flex flex-wrap items-center gap-1 px-2 pb-2">
                            <button
                                type="button"
                                onclick={() => fileInput?.click()}
                                disabled={http.processing}
                                class="inline-flex min-h-9 items-center gap-1.5 rounded-full px-2.5 text-xs font-bold text-muted-foreground transition hover:bg-background hover:text-foreground disabled:opacity-40"
                                aria-label={t('assistant.attachImage')}
                                title={t('assistant.attachImage')}
                            >
                                <ImageIcon class="size-4" />
                                {t('assistant.attachImage')}
                            </button>
                            <button
                                type="button"
                                onclick={toggleMic}
                                disabled={voice.processing}
                                aria-pressed={voice.recording}
                                class="inline-flex min-h-9 items-center gap-1.5 rounded-full px-2.5 text-xs font-bold transition disabled:opacity-40 {voice.recording
                                    ? 'animate-pulse bg-red-500 text-white'
                                    : 'text-muted-foreground hover:bg-background hover:text-foreground'}"
                                aria-label={voice.recording
                                    ? t('voice.stop')
                                    : voice.processing
                                      ? t('voice.transcribing')
                                      : t('assistant.recordAudio')}
                                title={voice.recording
                                    ? t('voice.stop')
                                    : voice.processing
                                      ? t('voice.transcribing')
                                      : t('assistant.recordAudio')}
                            >
                                {#if voice.processing}
                                    <LoaderCircle class="size-4 animate-spin" />
                                {:else}
                                    <Mic class="size-4" />
                                {/if}
                                {voice.recording
                                    ? t('voice.stop')
                                    : t('assistant.recordAudio')}
                            </button>
                            <button
                                type="button"
                                onclick={() => setVoiceAutoPlay(!voice.autoPlay)}
                                aria-pressed={voice.autoPlay}
                                class="inline-flex min-h-9 items-center gap-1.5 rounded-full px-2.5 text-xs font-bold transition {voice.autoPlay
                                    ? 'text-emerald-700 hover:bg-background dark:text-emerald-300'
                                    : 'text-muted-foreground hover:bg-background hover:text-foreground'}"
                                aria-label={t('voice.autoPlay')}
                                title={t('voice.autoPlay')}
                            >
                                {#if voice.autoPlay}
                                    <Volume2 class="size-4" />
                                {:else}
                                    <VolumeX class="size-4" />
                                {/if}
                                {t('assistant.speakReplies')}
                            </button>
                            <Popover>
                                <PopoverTrigger
                                    class="inline-flex min-h-9 items-center gap-1.5 rounded-full px-2.5 text-xs font-bold text-muted-foreground transition hover:bg-background hover:text-foreground"
                                    aria-label={t('voice.settings')}
                                    title={t('voice.settings')}
                                >
                                    <Settings2 class="size-4" />
                                    {t('assistant.options')}
                                </PopoverTrigger>
                                <PopoverContent align="end" class="w-72 gap-4 p-4">
                                    <div class="flex flex-col gap-2">
                                        <span
                                            class="text-xs font-bold text-muted-foreground"
                                        >
                                            {t('voice.language')}
                                        </span>
                                        <select
                                            value={voice.language}
                                            onchange={(event) =>
                                                setVoiceLanguage(
                                                    event.currentTarget.value,
                                                )}
                                            class="min-h-11 w-full rounded-xl bg-card px-3 text-sm font-bold text-foreground outline-none ring-1 ring-border focus:ring-2 focus:ring-emerald-400"
                                        >
                                            {#each voiceLanguageOptions as option (option.code)}
                                                <option value={option.code}>
                                                    {option.flag} {option.label}
                                                </option>
                                            {/each}
                                        </select>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <span
                                            class="text-xs font-bold text-muted-foreground"
                                        >
                                            {t('voice.gender')}
                                        </span>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button
                                                type="button"
                                                onclick={() =>
                                                    setVoiceGender('female')}
                                                class="min-h-10 rounded-xl text-sm font-bold ring-1 transition {voice.voiceGender ===
                                                'female'
                                                    ? 'bg-[#0b1e33] text-white ring-[#0b1e33]'
                                                    : 'bg-card text-secondary-foreground ring-border hover:ring-emerald-400'}"
                                            >
                                                {t('voice.female')}
                                            </button>
                                            <button
                                                type="button"
                                                onclick={() =>
                                                    setVoiceGender('male')}
                                                class="min-h-10 rounded-xl text-sm font-bold ring-1 transition {voice.voiceGender ===
                                                'male'
                                                    ? 'bg-[#0b1e33] text-white ring-[#0b1e33]'
                                                    : 'bg-card text-secondary-foreground ring-border hover:ring-emerald-400'}"
                                            >
                                                {t('voice.male')}
                                            </button>
                                        </div>
                                    </div>
                                    <label
                                        class="flex items-center justify-between gap-3"
                                    >
                                        <span
                                            class="text-sm font-bold text-foreground"
                                        >
                                            {t('voice.autoPlay')}
                                        </span>
                                        <Switch
                                            checked={voice.autoPlay}
                                            onCheckedChange={(checked) =>
                                                setVoiceAutoPlay(checked)}
                                        />
                                    </label>
                                </PopoverContent>
                            </Popover>
                        </div>
                    </div>
                    <button
                        type="submit"
                        disabled={http.processing || http.message.trim() === ''}
                        class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-110 active:scale-95 disabled:opacity-40"
                        aria-label={t('assistant.send')}
                        title={t('assistant.send')}
                    >
                        <Send class="size-5 rtl:-scale-x-100" />
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<Toaster />
