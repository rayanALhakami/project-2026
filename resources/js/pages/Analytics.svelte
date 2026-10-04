<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import CalendarDays from '@lucide/svelte/icons/calendar-days';
    import ChartColumn from '@lucide/svelte/icons/chart-column';
    import Check from '@lucide/svelte/icons/check';
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import Heart from '@lucide/svelte/icons/heart';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Route from '@lucide/svelte/icons/route';
    import TrendingUp from '@lucide/svelte/icons/trending-up';
    import AppHead from '@/components/AppHead.svelte';
    import SiteHeader from '@/components/SiteHeader.svelte';
    import { Toaster } from '@/components/ui/sonner';
    import { formatNumber, t } from '@/lib/i18n.svelte';
    import { categoryMeta } from '@/lib/tourism.svelte';
    import { dashboard, trips } from '@/routes';
    import type { PlaceCategory } from '@/types';
    import type { Component } from 'svelte';

    interface AnalyticsInterest {
        interest: string;
        count: number;
    }

    interface AnalyticsStats {
        trips_count: number;
        upcoming_trips_count: number;
        completed_items_count: number;
        total_items_count: number;
        cities_visited_count: number;
        favorite_places_count: number;
        top_interests: AnalyticsInterest[];
    }

    let { stats }: { stats: AnalyticsStats } = $props();

    const completionRate = $derived(
        stats.total_items_count > 0
            ? Math.round(
                  (stats.completed_items_count / stats.total_items_count) * 100,
              )
            : 0,
    );

    const maxInterestCount = $derived(
        Math.max(...stats.top_interests.map((interest) => interest.count), 1),
    );

    const statsCards = $derived.by((): {
        key: string;
        label: string;
        value: number;
        icon: Component<{ class?: string }>;
        tint: string;
    }[] => [
        {
            key: 'trips',
            label: t('analytics.trips'),
            value: stats.trips_count,
            icon: Route,
            tint: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400',
        },
        {
            key: 'upcoming',
            label: t('analytics.upcoming'),
            value: stats.upcoming_trips_count,
            icon: CalendarDays,
            tint: 'bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-400',
        },
        {
            key: 'completed',
            label: t('analytics.completed'),
            value: stats.completed_items_count,
            icon: Check,
            tint: 'bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-400',
        },
        {
            key: 'cities',
            label: t('analytics.cities'),
            value: stats.cities_visited_count,
            icon: MapPin,
            tint: 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400',
        },
        {
            key: 'favorites',
            label: t('analytics.favorites'),
            value: stats.favorite_places_count,
            icon: Heart,
            tint: 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400',
        },
    ]);

    function interestLabel(interest: string): string {
        return interest in categoryMeta
            ? t(`categories.${interest as PlaceCategory}`)
            : interest;
    }
</script>

<AppHead title={t('analytics.title')} />

<SiteHeader active="" />

<div class="min-h-dvh bg-background">
    <section
        class="bg-gradient-to-br from-[#16344f] via-[#0b2337] to-[#071523] pb-16 pt-28 text-white"
    >
        <div class="mx-auto w-full max-w-6xl px-4 md:px-6">
            <span
                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-emerald-200 ring-1 ring-white/15"
            >
                <ChartColumn class="size-4" aria-hidden="true" />
                {t('analytics.title')}
            </span>
            <h1 class="mt-4 text-3xl font-bold sm:text-4xl">
                {t('analytics.title')}
            </h1>
            <p class="mt-2 max-w-2xl text-slate-300">
                {t('analytics.subtitle')}
            </p>
        </div>
    </section>

    <div class="mx-auto -mt-8 w-full max-w-6xl px-4 pb-20 md:px-6">
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {#each statsCards as card (card.key)}
                <div
                    class="rounded-[20px] bg-card p-5 shadow-lg shadow-slate-900/5 ring-1 ring-border"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="flex size-11 shrink-0 items-center justify-center rounded-full {card.tint}"
                            aria-hidden="true"
                        >
                            <card.icon class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-muted-foreground">
                                {card.label}
                            </p>
                            <p class="text-2xl font-bold text-foreground">
                                {formatNumber(card.value)}
                            </p>
                        </div>
                    </div>
                </div>
            {/each}

            <div
                class="rounded-[20px] bg-card p-5 shadow-lg shadow-slate-900/5 ring-1 ring-border"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-full bg-violet-50 text-violet-700 dark:bg-violet-950/50 dark:text-violet-400"
                        aria-hidden="true"
                    >
                        <TrendingUp class="size-5" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-muted-foreground">
                            {t('analytics.completion')}
                        </p>
                        <p class="text-2xl font-bold text-foreground">
                            {formatNumber(completionRate)}%
                        </p>
                    </div>
                </div>
                <div
                    class="mt-4 h-2 overflow-hidden rounded-full bg-muted"
                    role="progressbar"
                    aria-valuenow={completionRate}
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-label={t('analytics.completion')}
                >
                    <div
                        class="h-full rounded-full bg-gradient-to-l from-emerald-400 to-teal-700 transition-all"
                        style="width: {completionRate}%"
                    ></div>
                </div>
            </div>
        </div>

        {#if stats.top_interests.length > 0}
            <section class="mt-8">
                <h2 class="text-xl font-bold text-foreground sm:text-2xl">
                    {t('analytics.topInterests')}
                </h2>
                <div
                    class="mt-4 rounded-[20px] bg-card p-5 shadow-lg shadow-slate-900/5 ring-1 ring-border"
                >
                    <ul class="flex flex-col gap-4">
                        {#each stats.top_interests as interest (interest.interest)}
                            {@const width = Math.round(
                                (interest.count / maxInterestCount) * 100,
                            )}
                            <li>
                                <div
                                    class="flex items-center justify-between gap-3 text-sm font-bold"
                                >
                                    <span class="text-foreground">
                                        {interestLabel(interest.interest)}
                                    </span>
                                    <span class="text-muted-foreground">
                                        {formatNumber(interest.count)}
                                    </span>
                                </div>
                                <div
                                    class="mt-1.5 h-2 overflow-hidden rounded-full bg-muted"
                                >
                                    <div
                                        class="h-full rounded-full bg-gradient-to-l from-emerald-400 to-teal-700 transition-all"
                                        style="width: {width}%"
                                    ></div>
                                </div>
                            </li>
                        {/each}
                    </ul>
                </div>
            </section>
        {:else if stats.trips_count === 0 && stats.favorite_places_count === 0}
            <div
                class="mt-8 rounded-[20px] border-2 border-dashed border-border bg-card p-10 text-center sm:p-12"
            >
                <span
                    class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300"
                >
                    <ChartColumn class="size-7" aria-hidden="true" />
                </span>
                <p class="mt-4 text-lg font-bold text-foreground">
                    {t('analytics.title')}
                </p>
                <p class="mt-1 text-base text-muted-foreground">
                    {t('analytics.subtitle')}
                </p>
                <Link
                    href={trips().url}
                    class="mt-6 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-6 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-110 active:scale-[0.98]"
                >
                    <Route class="size-4" aria-hidden="true" />
                    {t('trips.title')}
                </Link>
            </div>
        {/if}

        <div class="mt-8">
            <Link
                href={dashboard().url}
                class="inline-flex items-center gap-1.5 text-sm font-bold text-emerald-700 transition hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300"
            >
                <ChevronLeft class="size-4 rtl:rotate-180" aria-hidden="true" />
                {t('nav.home')}
            </Link>
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

<Toaster />
