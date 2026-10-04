<script lang="ts">
    import { onMount } from 'svelte';
    import type { Component } from 'svelte';
    import Accessibility from '@lucide/svelte/icons/accessibility';
    import BadgeCheck from '@lucide/svelte/icons/badge-check';
    import Landmark from '@lucide/svelte/icons/landmark';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Route from '@lucide/svelte/icons/route';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Users from '@lucide/svelte/icons/users';
    import CityMotif from '@/components/CityMotif.svelte';
    import ActionButton from '@/components/ActionButton.svelte';
    import { cityTheme } from '@/lib/city-theme';
    import { t } from '@/lib/i18n.svelte';

    const STORAGE_KEY = 'onboarding-v1';

    let visible = $state(false);
    let step = $state(0);

    const steps: {
        title: string;
        description: string;
        icon: Component<{ class?: string }>;
        cityId: number;
        emoji: string;
    }[] = [
        {
            title: t('onboarding.step1Title'),
            description: t('onboarding.step1Desc'),
            icon: Route,
            cityId: 3,
            emoji: '🐘',
        },
        {
            title: t('onboarding.step2Title'),
            description: t('onboarding.step2Desc'),
            icon: MapPin,
            cityId: 2,
            emoji: '📍',
        },
        {
            title: t('onboarding.step3Title'),
            description: t('onboarding.step3Desc'),
            icon: Sparkles,
            cityId: 1,
            emoji: '🎙️',
        },
    ];

    const trustBadges = [
        { key: 'unesco', label: t('badges.unesco'), icon: Landmark },
        { key: 'free', label: t('common.free'), icon: BadgeCheck },
        { key: 'family', label: t('common.family'), icon: Users },
        { key: 'wheelchair', label: t('badges.wheelchair'), icon: Accessibility },
    ];

    onMount(() => {
        if (!localStorage.getItem(STORAGE_KEY)) {
            visible = true;
        }
    });

    function finish(): void {
        localStorage.setItem(STORAGE_KEY, '1');
        visible = false;
    }

    function next(): void {
        if (step < steps.length - 1) {
            step += 1;

            return;
        }

        finish();
    }
</script>

<svelte:window
    onkeydown={(event) => {
        if (visible && event.key === 'Escape') {
            finish();
        }
    }}
/>

{#if visible}
    {@const Icon = steps[step].icon}
    <div
        class="fixed inset-0 z-[100] flex items-end justify-center bg-black/50 p-4 backdrop-blur-sm sm:items-center"
        role="dialog"
        aria-modal="true"
        aria-label={t('onboarding.title')}
    >
        <div
            class="w-full max-w-md rounded-[18px] border border-border bg-card p-6 elevation-raised"
        >
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-1.5" aria-hidden="true">
                    {#each steps as item, index (item.title)}
                        <span
                            class="h-2 rounded-full transition-all {index === step
                                ? 'w-6 bg-primary'
                                : 'w-2 bg-border'}"
                        ></span>
                    {/each}
                </div>
                <button
                    type="button"
                    onclick={finish}
                    class="min-h-10 rounded-full px-3 text-sm font-semibold text-muted-foreground transition hover:bg-muted hover:text-foreground"
                >
                    {t('onboarding.skip')}
                </button>
            </div>

            <div
                data-city={steps[step].cityId}
                class="city-tint relative mt-4 flex h-32 items-center justify-center overflow-hidden rounded-3xl"
            >
                <span class="text-5xl" aria-hidden="true">
                    {steps[step].emoji}
                </span>
                <div
                    class="city-ink pointer-events-none absolute inset-x-0 bottom-0 h-20 opacity-25"
                >
                    <CityMotif
                        type={cityTheme(steps[step].cityId).motif}
                        class="h-full w-full"
                    />
                </div>
            </div>

            <div class="mt-5 flex items-start gap-3">
                <span
                    class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary"
                >
                    <Icon class="size-6" />
                </span>
                <div>
                    <h2 class="text-xl font-semibold text-foreground">
                        {steps[step].title}
                    </h2>
                    <p class="mt-1 text-base text-muted-foreground">
                        {steps[step].description}
                    </p>
                </div>
            </div>

            {#if step === steps.length - 1}
                <ul class="mt-5 flex flex-wrap gap-2">
                    {#each trustBadges as badge (badge.key)}
                        <li
                            class="inline-flex items-center gap-1.5 rounded-full border border-border bg-card px-3 py-1 text-sm font-semibold text-foreground"
                        >
                            <badge.icon
                                class="size-4 text-primary"
                                aria-hidden="true"
                            />
                            {badge.label}
                        </li>
                    {/each}
                </ul>
            {/if}

            <ActionButton size="lg" onclick={next} class="mt-6 w-full">
                {step === steps.length - 1
                    ? t('onboarding.start')
                    : t('common.next')}
            </ActionButton>
        </div>
    </div>
{/if}
