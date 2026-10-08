<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import X from '@lucide/svelte/icons/x';
    import { onMount } from 'svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { t } from '@/lib/i18n.svelte';

    interface BeforeInstallPromptEvent extends Event {
        prompt: () => Promise<void>;
        userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
    }

    const DISMISSED_KEY = 'pwa-install-dismissed';

    let deferredPrompt = $state<BeforeInstallPromptEvent | null>(null);
    let installed = $state(false);
    let dismissed = $state(false);

    const onAssistantPage = $derived(page.url.startsWith('/assistant'));

    const visible = $derived(
        deferredPrompt !== null && !installed && !dismissed && !onAssistantPage,
    );

    function isStandalone(): boolean {
        const standaloneNavigator = window.navigator as Navigator & {
            standalone?: boolean;
        };

        return (
            window.matchMedia('(display-mode: standalone)').matches ||
            standaloneNavigator.standalone === true
        );
    }

    function dismiss(): void {
        dismissed = true;
        localStorage.setItem(DISMISSED_KEY, '1');
    }

    async function install(): Promise<void> {
        if (deferredPrompt === null) {
            return;
        }

        const promptEvent = deferredPrompt;
        deferredPrompt = null;
        await promptEvent.prompt();
        const choice = await promptEvent.userChoice;

        if (choice.outcome === 'accepted') {
            installed = true;
        }
    }

    onMount(() => {
        installed = isStandalone();
        dismissed = localStorage.getItem(DISMISSED_KEY) === '1';

        const onBeforeInstallPrompt = (event: Event) => {
            event.preventDefault();
            deferredPrompt = event as BeforeInstallPromptEvent;
        };

        const onAppInstalled = () => {
            installed = true;
            deferredPrompt = null;
        };

        window.addEventListener('beforeinstallprompt', onBeforeInstallPrompt);
        window.addEventListener('appinstalled', onAppInstalled);

        return () => {
            window.removeEventListener(
                'beforeinstallprompt',
                onBeforeInstallPrompt,
            );
            window.removeEventListener('appinstalled', onAppInstalled);
        };
    });
</script>

{#if visible}
    <aside
        class="fixed inset-x-4 bottom-20 z-40 sm:inset-x-auto sm:end-6 sm:bottom-6 sm:w-80"
    >
        <div class="relative rounded-2xl bg-card p-4 shadow-xl ring-1 ring-border">
            <button
                type="button"
                class="absolute end-3 top-3 inline-flex size-7 items-center justify-center rounded-full text-muted-foreground transition hover:bg-muted hover:text-foreground"
                aria-label={t('common.close')}
                onclick={dismiss}
            >
                <X class="size-4" />
            </button>

            <div class="flex items-start gap-3 pe-7">
                <span
                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 text-white"
                >
                    <AppLogoIcon class="size-5 fill-current" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold">{t('pwa.install')}</p>
                    <p
                        class="mt-0.5 text-xs leading-relaxed text-muted-foreground"
                    >
                        {t('pwa.hint')}
                    </p>
                </div>
            </div>

            <button
                type="button"
                class="mt-3 inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-950/20 transition hover:brightness-110 active:scale-[0.98]"
                onclick={install}
            >
                {t('pwa.install')}
            </button>
        </div>
    </aside>
{/if}
