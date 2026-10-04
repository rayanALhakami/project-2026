<script lang="ts">
    import ArrowLeftRight from '@lucide/svelte/icons/arrow-left-right';
    import Check from '@lucide/svelte/icons/check';
    import Copy from '@lucide/svelte/icons/copy';
    import Languages from '@lucide/svelte/icons/languages';
    import LoaderCircle from '@lucide/svelte/icons/loader-circle';
    import Mic from '@lucide/svelte/icons/mic';
    import Square from '@lucide/svelte/icons/square';
    import Volume2 from '@lucide/svelte/icons/volume-2';
    import { useHttp } from '@inertiajs/svelte';
    import { onDestroy } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import SiteHeader from '@/components/SiteHeader.svelte';
    import {
        formatNumber,
        getLocale,
        t,
        type LocaleCode,
    } from '@/lib/i18n.svelte';
    import {
        cancelRecording,
        languageTagForLocale,
        speak,
        startRecording,
        stopRecording,
        stopSpeaking,
        voice,
    } from '@/lib/voice.svelte';
    import { store as translateStore } from '@/routes/translate';

    interface TranslateResponse {
        translation: string;
        target_language: string;
        source_language: string | null;
    }

    const languages = [
        { code: 'ar', label: 'العربية', flag: '🇸🇦' },
        { code: 'en', label: 'English', flag: '🇬🇧' },
        { code: 'zh', label: '中文', flag: '🇨🇳' },
        { code: 'fr', label: 'Français', flag: '🇫🇷' },
        { code: 'es', label: 'Español', flag: '🇪🇸' },
        { code: 'ru', label: 'Русский', flag: '🇷🇺' },
        { code: 'tr', label: 'Türkçe', flag: '🇹🇷' },
        { code: 'ur', label: 'اردو', flag: '🇵🇰' },
        { code: 'hi', label: 'हिन्दी', flag: '🇮🇳' },
        { code: 'de', label: 'Deutsch', flag: '🇩🇪' },
    ];

    const MAX_CHARS = 5000;

    let source = $state('ar');
    let target = $state('en');
    let input = $state('');
    let output = $state('');
    let translated = $state(false);
    let copied = $state(false);
    let errorMessage = $state<string | null>(null);

    const isArabic = $derived(getLocale() === 'ar');

    const http = useHttp<
        { text: string; target_language: string; source_language: string },
        TranslateResponse
    >({
        text: '',
        target_language: 'en',
        source_language: 'ar',
    });

    onDestroy(() => {
        stopSpeaking();
        cancelRecording();
    });

    function failureText(): string {
        return isArabic
            ? 'تعذر إتمام الترجمة حالياً. حاول مرة أخرى بعد قليل.'
            : 'Translation failed. Please try again shortly.';
    }

    function parseErrorResponse(data: string): string {
        try {
            const payload = JSON.parse(data) as {
                error?: unknown;
                message?: unknown;
            };

            if (typeof payload.error === 'string' && payload.error !== '') {
                return payload.error;
            }

            if (
                typeof payload.message === 'string' &&
                payload.message !== ''
            ) {
                return payload.message;
            }
        } catch {
            // Fall back to the generic message below.
        }

        return failureText();
    }

    function swap(): void {
        stopSpeaking();
        [source, target] = [target, source];

        if (output) {
            input = output;
            output = '';
            translated = false;
        }

        errorMessage = null;
    }

    function run(): void {
        const value = input.trim();

        if (value === '' || http.processing) {
            return;
        }

        errorMessage = null;
        http.text = value;
        http.target_language = target;
        http.source_language = source;
        http.clearErrors();

        http.post(translateStore.url(), {
            onSuccess: (response) => {
                const translation =
                    typeof response?.translation === 'string'
                        ? response.translation.trim()
                        : '';

                output = translation;
                translated = translation !== '';
                errorMessage = translated ? null : failureText();
            },
            onHttpException: (response) => {
                errorMessage = parseErrorResponse(response.data);
                translated = false;
                output = '';
            },
            onNetworkError: () => {
                errorMessage = failureText();
                translated = false;
                output = '';
            },
        }).catch(() => {
            // Failures are surfaced through the callbacks above.
        });
    }

    async function copy(): Promise<void> {
        try {
            await navigator.clipboard.writeText(output);
            copied = true;
            setTimeout(() => (copied = false), 1500);
        } catch {
            copied = false;
        }
    }

    async function dictate(): Promise<void> {
        if (voice.processing) {
            return;
        }

        if (voice.recording) {
            const text = await stopRecording(
                languageTagForLocale(source as LocaleCode),
            );

            if (text === null) {
                if (voice.error) {
                    errorMessage = voice.error;
                }

                return;
            }

            if (text.trim() === '') {
                errorMessage =
                    voice.error ??
                    'لم يتم التعرف على أي كلام. حاول مرة أخرى.';

                return;
            }

            input = text;
            errorMessage = null;

            return;
        }

        stopSpeaking();
        errorMessage = null;

        try {
            await startRecording();
        } catch (error) {
            errorMessage =
                error instanceof Error
                    ? error.message
                    : 'تعذر بدء التسجيل الصوتي.';
        }
    }

    async function listen(): Promise<void> {
        if (voice.speaking) {
            stopSpeaking();

            return;
        }

        if (output.trim() === '') {
            return;
        }

        await speak(output, languageTagForLocale(target as LocaleCode));

        if (!voice.speaking && voice.error) {
            errorMessage = voice.error;
        }
    }

    const selectClass =
        'min-h-13 w-full rounded-xl bg-card px-4 text-base font-bold text-foreground outline-none ring-1 ring-border focus:ring-2 focus:ring-emerald-400';
    const paneButton =
        'flex min-h-11 items-center gap-2 rounded-xl bg-card px-4 text-sm font-bold text-secondary-foreground ring-1 ring-border transition hover:ring-emerald-400 active:scale-[0.98] disabled:opacity-50';
</script>

<AppHead title={t('translate.title')} />

<SiteHeader active="translate" />

<div class="min-h-dvh bg-background">
    <section
        class="bg-gradient-to-br from-[#16344f] via-[#0b2337] to-[#071523] pb-16 pt-28 text-white"
    >
        <div class="mx-auto w-full max-w-4xl px-4 md:px-6">
            <span
                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-emerald-200 ring-1 ring-white/15"
            >
                <Languages class="size-4" />
                {t('translate.subtitle')}
            </span>
            <h1 class="mt-4 text-3xl font-bold sm:text-4xl">
                {t('translate.title')}
            </h1>
        </div>
    </section>

    <div class="mx-auto -mt-8 w-full max-w-4xl px-4 pb-20 md:px-6">
        <div
            class="rounded-[20px] bg-card p-6 shadow-lg shadow-slate-900/5 ring-1 ring-border"
        >
            <div class="grid items-end gap-3 sm:grid-cols-[1fr_auto_1fr]">
                <label class="flex flex-col gap-2">
                    <span class="text-xs font-bold text-muted-foreground">
                        {t('translate.from')}
                    </span>
                    <select bind:value={source} class={selectClass}>
                        {#each languages as lang (lang.code)}
                            <option value={lang.code}>
                                {lang.flag} {lang.label}
                            </option>
                        {/each}
                    </select>
                </label>

                <button
                    type="button"
                    onclick={swap}
                    class="mx-auto flex size-12 items-center justify-center rounded-xl bg-[#0b1e33] text-white transition hover:bg-[#12293f] active:scale-95"
                    aria-label={t('translate.swap')}
                    title={t('translate.swap')}
                >
                    <ArrowLeftRight class="size-5" />
                </button>

                <label class="flex flex-col gap-2">
                    <span class="text-xs font-bold text-muted-foreground">
                        {t('translate.to')}
                    </span>
                    <select bind:value={target} class={selectClass}>
                        {#each languages as lang (lang.code)}
                            <option value={lang.code}>
                                {lang.flag} {lang.label}
                            </option>
                        {/each}
                    </select>
                </label>
            </div>

            {#if errorMessage}
                <p
                    class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-700 ring-1 ring-red-100 dark:bg-red-950/40 dark:text-red-300 dark:ring-red-900/60"
                >
                    {errorMessage}
                </p>
            {/if}

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div
                    class="flex min-h-56 flex-col rounded-[18px] bg-card p-4 ring-1 ring-border focus-within:ring-2 focus-within:ring-emerald-400"
                >
                    <textarea
                        bind:value={input}
                        rows="6"
                        maxlength={MAX_CHARS}
                        placeholder={t('translate.placeholder')}
                        class="w-full flex-1 resize-none bg-transparent text-lg text-foreground outline-none placeholder:text-muted-foreground"
                    ></textarea>
                    <div class="mt-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                onclick={dictate}
                                disabled={voice.processing}
                                aria-pressed={voice.recording}
                                class="flex size-10 items-center justify-center rounded-xl transition disabled:opacity-40 {voice.recording
                                    ? 'animate-pulse bg-red-500 text-white'
                                    : 'text-muted-foreground ring-1 ring-border hover:text-foreground'}"
                                aria-label={voice.recording
                                    ? t('voice.stop')
                                    : voice.processing
                                      ? t('voice.transcribing')
                                      : t('voice.dictate')}
                                title={voice.recording
                                    ? t('voice.stop')
                                    : voice.processing
                                      ? t('voice.transcribing')
                                      : t('voice.dictate')}
                            >
                                {#if voice.processing}
                                    <LoaderCircle class="size-5 animate-spin" />
                                {:else}
                                    <Mic class="size-5" />
                                {/if}
                            </button>
                            <span class="text-xs font-bold text-muted-foreground">
                                {t('translate.chars', {
                                    count: formatNumber(input.length),
                                })}
                            </span>
                        </div>
                        <button
                            type="button"
                            onclick={run}
                            disabled={http.processing ||
                                input.trim() === ''}
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-6 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-110 active:scale-[0.98] disabled:opacity-50"
                        >
                            {#if http.processing}
                                <LoaderCircle class="size-5 animate-spin" />
                            {/if}
                            {t('translate.action')}
                        </button>
                    </div>
                </div>

                <div
                    class="flex min-h-56 flex-col rounded-[18px] bg-[#0b1e33] p-4 text-white"
                >
                    {#if translated}
                        <p class="flex-1 text-lg whitespace-pre-wrap">
                            {output}
                        </p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <button type="button" onclick={copy} class={paneButton}>
                                {#if copied}
                                    <Check class="size-5" />
                                    {t('translate.copied')}
                                {:else}
                                    <Copy class="size-5" />
                                    {t('translate.copy')}
                                {/if}
                            </button>
                            <button
                                type="button"
                                onclick={listen}
                                disabled={voice.processing}
                                class={paneButton}
                            >
                                {#if voice.speaking}
                                    <Square class="size-5" />
                                    {t('voice.stop')}
                                {:else}
                                    <Volume2 class="size-5" />
                                    {t('translate.listen')}
                                {/if}
                            </button>
                        </div>
                    {:else}
                        <p class="flex-1 text-sm text-slate-300">
                            {t('translate.outputPlaceholder')}
                        </p>
                    {/if}
                </div>
            </div>
        </div>
    </div>

    <footer
        class="border-t border-white/10 bg-[#08131f] pt-6 pb-bottom-nav text-slate-400 md:pb-6"
    >
        <div
            class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 text-xs sm:flex-row md:px-6"
        >
            <p>© {new Date().getFullYear()} {t('app.name')}</p>
            <p>{t('preview.rights')}</p>
        </div>
    </footer>
</div>
