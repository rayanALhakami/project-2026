<script lang="ts">
    import { Link, page, useHttp } from '@inertiajs/svelte';
    import ArrowDown from '@lucide/svelte/icons/arrow-down';
    import ArrowUp from '@lucide/svelte/icons/arrow-up';
    import Check from '@lucide/svelte/icons/check';
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import Heart from '@lucide/svelte/icons/heart';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Route from '@lucide/svelte/icons/route';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Users from '@lucide/svelte/icons/users';
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import PlaceDetailsSheet from '@/components/PlaceDetailsSheet.svelte';
    import PlaceShowcaseCard from '@/components/PlaceShowcaseCard.svelte';
    import SiteHeader from '@/components/SiteHeader.svelte';
    import { Toaster } from '@/components/ui/sonner';
    import WeatherArt from '@/components/WeatherArt.svelte';
    import WeatherCityPicker from '@/components/WeatherCityPicker.svelte';
    import { selectedCityId, setSelectedCity } from '@/lib/city.svelte';
    import { cityImage } from '@/lib/city-images';
    import { formatDate, formatNumber, getLocale, t } from '@/lib/i18n.svelte';
    import { cityName, weatherCondition } from '@/lib/localize';
    import {
        cities,
        placesByCity,
        topPlaces,
    } from '@/lib/tourism.svelte';
    import {
        retryWeather,
        selectedWeather,
        weatherError,
    } from '@/lib/weather-city.svelte';
    import { data as analyticsData } from '@/routes/analytics';
    import {
        analytics as analyticsRoute,
        assistant,
        places,
        trips,
    } from '@/routes';
    import type { Component } from 'svelte';
    import type { WeatherCondition } from '@/types';

    interface SavedTrip {
        id: number;
        title: string;
        city_name: string | null;
        city_name_en: string | null;
        travelers_count: number;
        days_count: number;
        start_date: string;
        end_date: string;
        budget: string | null;
        items_count: number;
        completed_count: number;
    }

    interface AnalyticsInterest {
        interest: string;
        count: number;
    }

    interface AnalyticsData {
        trips_count: number;
        upcoming_trips_count: number;
        completed_items_count: number;
        total_items_count: number;
        cities_visited_count: number;
        favorite_places_count: number;
        top_interests: AnalyticsInterest[];
    }

    let {
        savedTrip = null,
    }: {
        savedTrip?: SavedTrip | null;
    } = $props();

    const analyticsHttp = useHttp<Record<string, never>, AnalyticsData>({});
    let analytics = $state<AnalyticsData | null>(null);
    let analyticsLoading = $state(true);

    onMount(() => {
        analyticsHttp
            .get(analyticsData.url(), {
                onSuccess: (response) => {
                    analytics = response;
                },
                onFinish: () => {
                    analyticsLoading = false;
                },
            })
            .catch(() => {
                analyticsLoading = false;
            });
    });

    const user = $derived(page.props.auth.user);
    const isArabic = $derived(getLocale() === 'ar');
    const city = $derived(
        cities.find((item) => item.id === selectedCityId()) ?? cities[0],
    );
    const weather = $derived(selectedWeather());
    const weatherFailure = $derived(weatherError());
    const artCondition = $derived(
        (weather?.condition ?? 'clear') as WeatherCondition,
    );

    const recommended = $derived.by(() => {
        const local = [...placesByCity(selectedCityId())].sort(
            (a, b) => b.rating - a.rating,
        );

        return local.length > 0 ? local.slice(0, 6) : topPlaces(6);
    });

    const analyticsStats = $derived.by((): {
        key: string;
        label: string;
        value: number;
        icon: Component<{ class?: string }>;
    }[] => {
        if (!analytics) {
            return [];
        }

        return [
            {
                key: 'trips',
                label: t('analytics.trips'),
                value: analytics.trips_count,
                icon: Route,
            },
            {
                key: 'completed',
                label: t('analytics.completed'),
                value: analytics.completed_items_count,
                icon: Check,
            },
            {
                key: 'favorites',
                label: t('analytics.favorites'),
                value: analytics.favorite_places_count,
                icon: Heart,
            },
            {
                key: 'cities',
                label: t('analytics.cities'),
                value: analytics.cities_visited_count,
                icon: MapPin,
            },
        ];
    });

    function onCityChange(event: Event): void {
        setSelectedCity(Number((event.currentTarget as HTMLSelectElement).value));
    }
</script>

<AppHead title={t('nav.home')} />

<SiteHeader active="home" transparent />

<div class="min-h-dvh bg-background">
    <section
        class="bg-gradient-to-br from-[#16344f] via-[#0b2337] to-[#071523] pb-16 pt-28 text-white"
    >
        <div class="mx-auto w-full max-w-6xl px-4 md:px-6">
            <span
                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-emerald-200 ring-1 ring-white/15"
            >
                <Sparkles class="size-4" />
                {t('app.nameAr')}
            </span>
            <h1 class="mt-4 text-3xl font-bold sm:text-4xl">
                {t('dashboard.greeting')} {user?.name ?? t('common.guest')} 👋
            </h1>
            <p class="mt-2 max-w-2xl text-slate-300">
                {t('dashboard.subtitle')}
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                <label class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-300">
                        {t('dashboard.whereTo')}
                    </span>
                    <select
                        value={selectedCityId()}
                        onchange={onCityChange}
                        class="appearance-none rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/15"
                    >
                        {#each cities as option (option.id)}
                            <option class="text-foreground" value={option.id}>
                                {cityName(option)}
                            </option>
                        {/each}
                    </select>
                </label>

                <div class="flex flex-wrap gap-3">
                    <Link
                        href={places().url}
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-5 text-sm font-bold text-white shadow-lg shadow-emerald-950/30 transition hover:brightness-110 active:scale-[0.98]"
                    >
                        <MapPin class="size-4" />
                        {t('dashboard.explorePlaces', { city: cityName(city) })}
                    </Link>
                    <Link
                        href={trips().url}
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/25 px-5 text-sm font-bold text-white transition hover:bg-white/10 active:scale-[0.98]"
                    >
                        <Route class="size-4" />
                        {t('dashboard.planTrip')}
                    </Link>
                </div>
            </div>
        </div>
    </section>

    <div class="mx-auto -mt-8 w-full max-w-6xl px-4 pb-20 md:px-6">
        <div class="grid gap-5 md:grid-cols-2">
            <WeatherArt condition={artCondition} class="rounded-[20px]">
                <div class="flex flex-1 flex-col justify-between gap-4 p-4 sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-x-3 gap-y-2">
                        <div class="flex flex-col items-start gap-1">
                            <p class="text-base font-medium text-white/85">
                                {t('dashboard.weatherNow')}
                            </p>
                            <WeatherCityPicker />
                        </div>
                        {#if weather}
                            <span
                                class="inline-flex min-h-11 shrink-0 items-center rounded-full border border-white/20 bg-black/10 px-4 py-2 text-base font-semibold text-white backdrop-blur-sm"
                            >
                                {weatherCondition(weather)}
                            </span>
                        {:else}
                            <span
                                class="inline-block h-11 w-28 animate-pulse rounded-full bg-white/15"
                                aria-hidden="true"
                            ></span>
                        {/if}
                    </div>

                    {#if weather}
                        <div class="flex items-center justify-between gap-3 sm:gap-5">
                            <p
                                class="text-6xl leading-[0.95] font-semibold text-white sm:text-7xl"
                            >
                                {formatNumber(weather.temperature)}°
                            </p>
                            <span
                                class="flex size-20 shrink-0 items-center justify-center rounded-full border border-white/20 bg-white/12 text-5xl backdrop-blur-sm sm:size-24 sm:text-6xl"
                            >
                                {weather.icon}
                            </span>
                        </div>

                        <div
                            class="grid grid-cols-2 gap-3 border-t border-white/20 pt-3"
                        >
                            <div
                                class="flex min-h-16 flex-col items-start justify-center gap-1 rounded-2xl border border-white/15 bg-white/10 px-3 py-2 backdrop-blur-sm sm:flex-row sm:items-center sm:justify-between sm:px-4"
                            >
                                <span
                                    class="flex items-center gap-2 text-base text-white/90"
                                >
                                    <ArrowUp class="size-4" aria-hidden="true" />
                                    {t('weather.high')}
                                </span>
                                <span class="text-xl font-semibold text-white">
                                    {formatNumber(weather.high)}°
                                </span>
                            </div>
                            <div
                                class="flex min-h-16 flex-col items-start justify-center gap-1 rounded-2xl border border-white/15 bg-white/10 px-3 py-2 backdrop-blur-sm sm:flex-row sm:items-center sm:justify-between sm:px-4"
                            >
                                <span
                                    class="flex items-center gap-2 text-base text-white/90"
                                >
                                    <ArrowDown class="size-4" aria-hidden="true" />
                                    {t('weather.low')}
                                </span>
                                <span class="text-xl font-semibold text-white">
                                    {formatNumber(weather.low)}°
                                </span>
                            </div>
                        </div>
                    {:else if weatherFailure}
                        <div
                            class="flex flex-1 flex-col items-start justify-center gap-3 py-6"
                            role="alert"
                        >
                            <p class="text-base font-medium text-white/90">
                                {weatherFailure}
                            </p>
                            <button
                                type="button"
                                onclick={() => void retryWeather()}
                                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/25 bg-white/10 px-5 text-sm font-bold text-white backdrop-blur-sm transition hover:bg-white/20 active:scale-[0.98]"
                            >
                                {t('weather.retry')}
                            </button>
                        </div>
                    {:else}
                        <div
                            class="flex items-center justify-between gap-3 sm:gap-5"
                            aria-hidden="true"
                        >
                            <span
                                class="h-14 w-32 animate-pulse rounded-2xl bg-white/15 sm:h-16 sm:w-36"
                            ></span>
                            <span
                                class="size-20 shrink-0 animate-pulse rounded-full bg-white/15 sm:size-24"
                            ></span>
                        </div>

                        <div
                            class="grid grid-cols-2 gap-3 border-t border-white/20 pt-3"
                            aria-hidden="true"
                        >
                            <span
                                class="min-h-16 animate-pulse rounded-2xl bg-white/10"
                            ></span>
                            <span
                                class="min-h-16 animate-pulse rounded-2xl bg-white/10"
                            ></span>
                        </div>
                    {/if}
                </div>
            </WeatherArt>

            {#if savedTrip}
                <div
                    class="flex flex-col rounded-[20px] bg-card p-6 shadow-sm ring-1 ring-border"
                >
                    <p class="text-sm font-bold text-muted-foreground">
                        {t('dashboard.upcomingTrip')}
                    </p>
                    <p class="mt-2 text-lg font-bold text-foreground">
                        {savedTrip.title}
                    </p>
                    <p class="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground">
                        <MapPin class="size-4" />
                        {isArabic
                            ? savedTrip.city_name
                            : savedTrip.city_name_en}
                    </p>
                    <div
                        class="mt-3 flex flex-wrap items-center gap-4 text-sm text-muted-foreground"
                    >
                        <span class="flex items-center gap-1.5">
                            <Users class="size-4" />
                            {formatNumber(savedTrip.travelers_count)}
                            {t('common.persons')}
                        </span>
                        <span>
                            {formatNumber(savedTrip.days_count)}
                            {t('common.days')}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-muted-foreground">
                        {formatDate(savedTrip.start_date)} — {formatDate(
                            savedTrip.end_date,
                        )}
                    </p>
                    {#if savedTrip.items_count > 0}
                        <div class="mt-3">
                            <p class="text-xs font-bold text-muted-foreground">
                                {t('trips.progress', {
                                    done: formatNumber(
                                        savedTrip.completed_count,
                                    ),
                                    total: formatNumber(savedTrip.items_count),
                                })}
                            </p>
                            <div
                                class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-muted"
                            >
                                <div
                                    class="h-full rounded-full bg-gradient-to-l from-emerald-400 to-teal-700"
                                    style="width: {Math.round(
                                        (savedTrip.completed_count /
                                            savedTrip.items_count) *
                                            100,
                                    )}%"
                                ></div>
                            </div>
                        </div>
                    {/if}
                    <Link
                        href={trips().url}
                        class="mt-5 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-[#0b1e33] px-5 text-sm font-bold text-white transition hover:bg-[#12293f] active:scale-[0.98]"
                    >
                        {t('dashboard.openTrip')}
                        <ChevronLeft class="size-4 ltr:rotate-180" />
                    </Link>
                </div>
            {:else}
                <div
                    class="flex flex-col rounded-[20px] bg-card p-6 shadow-sm ring-1 ring-border"
                >
                    <p class="text-sm font-bold text-muted-foreground">
                        {t('dashboard.upcomingTrip')}
                    </p>
                    <p class="mt-2 text-lg font-bold text-foreground">
                        {t('dashboard.noTrip')}
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {t('dashboard.planFirst')}
                    </p>
                    <Link
                        href={trips().url}
                        class="mt-5 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-5 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-110 active:scale-[0.98]"
                    >
                        <Route class="size-4" />
                        {t('dashboard.planTrip')}
                    </Link>
                </div>
            {/if}
        </div>

        {#if analyticsLoading || analytics}
            <section class="mt-10">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-xl font-bold text-foreground sm:text-2xl">
                        {t('analytics.title')}
                    </h2>
                    <Link
                        href={analyticsRoute().url}
                        class="inline-flex items-center gap-1 text-sm font-bold text-emerald-700 transition hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300"
                    >
                        {t('analytics.title')}
                        <ChevronLeft class="size-4 ltr:rotate-180" />
                    </Link>
                </div>

                {#if analytics}
                    <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                        {#each analyticsStats as stat (stat.key)}
                            <div
                                class="flex items-center gap-3 rounded-[18px] bg-card p-4 shadow-sm ring-1 ring-border"
                            >
                                <span
                                    class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                                >
                                    <stat.icon class="size-5" />
                                </span>
                                <span class="min-w-0">
                                    <span
                                        class="block text-xs font-bold text-muted-foreground"
                                    >
                                        {stat.label}
                                    </span>
                                    <span
                                        class="block text-xl font-bold text-foreground"
                                    >
                                        {formatNumber(stat.value)}
                                    </span>
                                </span>
                            </div>
                        {/each}
                    </div>

                    {#if analytics.top_interests.length > 0}
                        <div
                            class="mt-3 flex flex-wrap items-center gap-2 rounded-[18px] bg-card p-4 shadow-sm ring-1 ring-border"
                        >
                            <span
                                class="text-sm font-bold text-muted-foreground"
                            >
                                {t('analytics.topInterests')}
                            </span>
                            {#each analytics.top_interests as interest (interest.interest)}
                                <span
                                    class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-bold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300"
                                >
                                    {interest.interest} · {formatNumber(
                                        interest.count,
                                    )}
                                </span>
                            {/each}
                        </div>
                    {/if}
                {:else}
                    <div
                        class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4"
                        aria-busy="true"
                        aria-live="polite"
                    >
                        {#each Array.from({ length: 4 }) as _, index (index)}
                            <div
                                class="rounded-[18px] bg-card p-4 shadow-sm ring-1 ring-border"
                            >
                                <span
                                    class="block size-11 animate-pulse rounded-xl bg-muted"
                                ></span>
                                <span
                                    class="mt-3 block h-3 w-16 animate-pulse rounded-full bg-muted"
                                ></span>
                                <span
                                    class="mt-2 block h-6 w-12 animate-pulse rounded-full bg-muted"
                                ></span>
                            </div>
                        {/each}
                    </div>
                {/if}
            </section>
        {/if}

        <section class="mt-10">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-bold text-foreground sm:text-2xl">
                    {t('dashboard.aroundCity', { city: cityName(city) })}
                </h2>
                <Link
                    href={places().url}
                    class="inline-flex items-center gap-1 text-sm font-bold text-emerald-700 dark:text-emerald-400 transition hover:text-emerald-800 dark:hover:text-emerald-300"
                >
                    {t('common.viewAll')}
                    <ChevronLeft class="size-4 ltr:rotate-180" />
                </Link>
            </div>
            <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                {#each recommended as place (place.id)}
                    <PlaceShowcaseCard {place} />
                {/each}
            </div>
        </section>

        <section
            class="mt-10 flex flex-col items-start gap-4 rounded-[24px] bg-[#0b1e33] p-6 text-white sm:flex-row sm:items-center sm:justify-between sm:p-8"
        >
            <div class="flex items-center gap-4">
                <span
                    class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-white/10 text-emerald-300 ring-1 ring-white/15"
                >
                    <Sparkles class="size-6" />
                </span>
                <div>
                    <p class="text-lg font-bold">
                        {t('dashboard.needHelp')}
                    </p>
                    <p class="text-sm text-slate-300">
                        {t('dashboard.helpDesc')}
                    </p>
                </div>
            </div>
            <Link
                href={assistant().url}
                class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-6 text-sm font-bold text-white transition hover:brightness-110 active:scale-[0.98] sm:w-auto"
            >
                <Sparkles class="size-4" />
                {t('dashboard.askAssistant')}
            </Link>
        </section>

        <section class="mt-10">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-bold text-foreground sm:text-2xl">
                    {t('dashboard.destinations')}
                </h2>
                <Link
                    href={places().url}
                    class="inline-flex items-center gap-1 text-sm font-bold text-emerald-700 dark:text-emerald-400 transition hover:text-emerald-800 dark:hover:text-emerald-300"
                >
                    {t('nav.places')}
                    <ChevronLeft class="size-4 ltr:rotate-180" />
                </Link>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
                {#each cities as destination (destination.id)}
                    <Link
                        href={places().url}
                        onclick={() => setSelectedCity(destination.id)}
                        class="group relative h-36 overflow-hidden rounded-[18px] shadow-sm ring-1 ring-border transition hover:-translate-y-0.5 hover:shadow-md"
                    >
                        {#if cityImage(destination.id)}
                            <img
                                src={cityImage(destination.id)}
                                alt={cityName(destination)}
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                loading="lazy"
                                decoding="async"
                            />
                        {/if}
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/15 to-transparent"
                        ></div>
                        <div
                            class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-2 p-3"
                        >
                            <span class="text-base font-bold text-white drop-shadow">
                                {cityName(destination)}
                            </span>
                            <span
                                class="rounded-full bg-card/90 px-2.5 py-0.5 text-[10px] font-bold text-foreground"
                            >
                                {t('places.count', {
                                    count: formatNumber(
                                        placesByCity(destination.id).length,
                                    ),
                                })}
                            </span>
                        </div>
                    </Link>
                {/each}
            </div>
        </section>
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

<PlaceDetailsSheet />
<Toaster />
