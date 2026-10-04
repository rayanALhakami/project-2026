<script lang="ts">
    import type { Component } from 'svelte';
    import { Link, page } from '@inertiajs/svelte';
    import Languages from '@lucide/svelte/icons/languages';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Route from '@lucide/svelte/icons/route';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import AppHead from '@/components/AppHead.svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import CityMotif from '@/components/CityMotif.svelte';
    import LanguageSwitcher from '@/components/LanguageSwitcher.svelte';
    import SaudiFlag from '@/components/SaudiFlag.svelte';
    import ThemeToggle from '@/components/ThemeToggle.svelte';
    import { t } from '@/lib/i18n.svelte';
    import { toUrl } from '@/lib/utils';
    import { dashboard, login, register } from '@/routes';

    const auth = $derived(page.props.auth);

    const features: {
        title: string;
        desc: string;
        icon: Component<{ class?: string }>;
    }[] = [
        {
            title: t('welcome.feature1Title'),
            desc: t('welcome.feature1Desc'),
            icon: Route,
        },
        {
            title: t('welcome.feature2Title'),
            desc: t('welcome.feature2Desc'),
            icon: MapPin,
        },
        {
            title: t('welcome.feature3Title'),
            desc: t('welcome.feature3Desc'),
            icon: Sparkles,
        },
    ];
</script>

<AppHead title={t('app.name')} />

<div class="flex min-h-dvh flex-col bg-background">
    <header
        class="flex items-center justify-between gap-2 px-4 py-3 md:px-8"
    >
        <div class="flex min-w-0 items-center gap-2">
            <span
                class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-primary text-primary-foreground"
            >
                <AppLogoIcon class="size-6 fill-current" />
            </span>
            <span
                class="text-base leading-tight font-semibold text-foreground sm:text-lg"
            >
                {t('app.name')}
            </span>
        </div>
        <div class="flex items-center gap-1">
            <LanguageSwitcher />
            <ThemeToggle />
        </div>
    </header>

    <main class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-8 px-4 pb-12 md:px-8">
        <section
            class="relative overflow-hidden rounded-[18px] bg-hero p-6 text-hero-foreground sm:p-10"
        >
            <div
                class="pointer-events-none absolute inset-x-0 bottom-0 h-32 text-white opacity-15"
            >
                <CityMotif type="skyline" class="h-full w-full" />
            </div>
            <div class="relative flex flex-col gap-5">
                <span
                    class="inline-flex w-fit items-center gap-2 rounded-full bg-white/15 px-4 py-1.5 text-sm font-semibold"
                >
                    <Sparkles class="size-4" />
                    {t('app.nameAr')}
                </span>
                <h1 class="max-w-2xl text-3xl font-semibold leading-tight sm:text-5xl">
                    {t('welcome.title')}
                </h1>
                <p class="max-w-xl text-base opacity-90 sm:text-lg">
                    {t('welcome.subtitle')}
                </p>
                <div class="flex flex-wrap gap-3">
                    {#if auth.user}
                        <Link
                            href={toUrl(dashboard())}
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-white px-6 text-base font-semibold text-hero transition active:scale-95"
                        >
                            {t('welcome.browse')}
                        </Link>
                    {:else}
                        <Link
                            href={toUrl(register())}
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-white px-6 text-base font-semibold text-hero transition active:scale-95"
                        >
                            {t('welcome.start')}
                        </Link>
                        <Link
                            href={toUrl(login())}
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border-2 border-white/60 px-6 text-base font-semibold text-white transition active:scale-95"
                        >
                            {t('welcome.login')}
                        </Link>
                    {/if}
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-3">
            {#each features as feature (feature.title)}
                <div
                    class="flex flex-col gap-3 rounded-[18px] border border-border bg-card p-5"
                >
                    <span
                        class="flex size-12 items-center justify-center rounded-2xl bg-primary/10 text-primary"
                    >
                        <feature.icon class="size-6" />
                    </span>
                    <h2 class="text-lg font-semibold text-foreground">
                        {feature.title}
                    </h2>
                    <p class="text-base text-muted-foreground">
                        {feature.desc}
                    </p>
                </div>
            {/each}
        </section>

        <section
            class="flex flex-col items-start gap-3 rounded-[18px] bg-secondary p-6 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex items-center gap-3">
                <span
                    class="flex size-12 items-center justify-center rounded-2xl bg-primary/10 text-primary"
                >
                    <Languages class="size-6" />
                </span>
                <p class="text-base font-semibold text-foreground">
                    {t('translate.subtitle')}
                </p>
            </div>
            <Link
                href={toUrl(auth.user ? dashboard() : register())}
                class="inline-flex min-h-12 items-center justify-center rounded-full bg-primary px-6 text-base font-semibold text-primary-foreground transition active:scale-95"
            >
                {auth.user ? t('welcome.browse') : t('welcome.start')}
            </Link>
        </section>
    </main>

    <footer
        class="flex flex-col items-center gap-3 border-t border-border px-4 py-6 text-center text-sm text-muted-foreground md:px-8"
    >
        <SaudiFlag class="h-10 w-[3.75rem]" />
        <p>{t('app.name')} — {t('welcome.subtitle')}</p>
    </footer>
</div>
