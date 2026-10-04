<script lang="ts">
    import List from '@lucide/svelte/icons/list';
    import LocateFixed from '@lucide/svelte/icons/locate-fixed';
    import Map from '@lucide/svelte/icons/map';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Search from '@lucide/svelte/icons/search';
    import X from '@lucide/svelte/icons/x';
    import { toast } from 'svelte-sonner';
    import AppHead from '@/components/AppHead.svelte';
    import PlaceCardSkeleton from '@/components/PlaceCardSkeleton.svelte';
    import PlaceDetailsSheet from '@/components/PlaceDetailsSheet.svelte';
    import PlaceShowcaseCard from '@/components/PlaceShowcaseCard.svelte';
    import PlacesMap from '@/components/PlacesMap.svelte';
    import SiteHeader from '@/components/SiteHeader.svelte';
    import { Toaster } from '@/components/ui/sonner';
    import { selectedCityId, setSelectedCity } from '@/lib/city.svelte';
    import { nearestCity, sortPlacesByDistance } from '@/lib/geo';
    import { formatNumber, t } from '@/lib/i18n.svelte';
    import { cityName } from '@/lib/localize';
    import { openPlaceSheet } from '@/lib/place-sheet.svelte';
    import {
        categoryMeta,
        cities,
        places as allPlaces,
    } from '@/lib/tourism.svelte';
    import type { Coordinates, PlaceCategory, PlaceWithDistance } from '@/types';

    let query = $state('');
    let activeCategory = $state<PlaceCategory | 'all'>('all');
    let activeCity = $state<number | 'all'>(selectedCityId());
    let coords = $state<Coordinates | null>(null);
    let locating = $state(false);
    let view = $state<'list' | 'map'>('list');

    const categories = Object.entries(categoryMeta) as [
        PlaceCategory,
        (typeof categoryMeta)[PlaceCategory],
    ][];

    const filtered = $derived(
        allPlaces.filter((place) => {
            const trimmed = query.trim().toLowerCase();
            const matchesQuery =
                trimmed === '' ||
                place.name.includes(query.trim()) ||
                place.nameEn.toLowerCase().includes(trimmed) ||
                place.tags.some((tag) => tag.includes(query.trim()));
            const matchesCategory =
                activeCategory === 'all' || place.category === activeCategory;
            const matchesCity =
                activeCity === 'all' || place.cityId === activeCity;

            return matchesQuery && matchesCategory && matchesCity;
        }),
    );

    const results = $derived.by((): PlaceWithDistance[] => {
        if (!coords) {
            return filtered.map((place) => ({ place, distanceKm: 0 }));
        }

        return sortPlacesByDistance(filtered, coords);
    });

    const hasCoords = $derived(coords !== null);

    function chooseCity(id: number | 'all'): void {
        activeCity = id;

        if (id !== 'all') {
            setSelectedCity(id);
        }
    }

    function clearLocation(): void {
        coords = null;
    }

    function findNearMe(): void {
        if (typeof navigator === 'undefined' || !('geolocation' in navigator)) {
            toast.error(t('places.locationUnsupported'));

            return;
        }

        locating = true;

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const next: Coordinates = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                };

                coords = next;
                locating = false;

                const near = nearestCity(cities, next);

                if (near) {
                    setSelectedCity(near.id);
                    chooseCity(near.id);
                    toast.success(
                        t('places.nearestCity', { city: cityName(near) }),
                    );
                }
            },
            () => {
                locating = false;
                toast.error(t('places.locationDenied'));
            },
            { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 },
        );
    }

    const chipBase =
        'min-h-10 rounded-full px-4 text-sm font-bold ring-1 transition active:scale-[0.98]';
</script>

<AppHead title={t('places.title')} />

<SiteHeader active="places" />

<div class="min-h-dvh bg-background">
    <section
        class="bg-gradient-to-br from-[#16344f] via-[#0b2337] to-[#071523] pb-16 pt-28 text-white"
    >
        <div class="mx-auto w-full max-w-6xl px-4 md:px-6">
            <span
                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-emerald-200 ring-1 ring-white/15"
            >
                <MapPin class="size-4" />
                {t('preview.destinationsBadge')}
            </span>
            <h1 class="mt-4 text-3xl font-bold sm:text-4xl">
                {t('places.title')}
            </h1>
            <p class="mt-2 max-w-2xl text-slate-300">
                {t('places.subtitle')}
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                <div
                    class="flex flex-1 items-center gap-3 rounded-xl bg-card/95 px-4 py-3 ring-1 ring-white/20 focus-within:ring-2 focus-within:ring-emerald-400"
                >
                    <Search class="size-5 shrink-0 text-muted-foreground" />
                    <input
                        bind:value={query}
                        type="text"
                        aria-label={t('places.searchPlaceholder')}
                        placeholder={t('places.searchPlaceholder')}
                        class="w-full bg-transparent text-base text-foreground outline-none placeholder:text-muted-foreground"
                    />
                </div>
                <button
                    type="button"
                    onclick={findNearMe}
                    disabled={locating}
                    class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-6 text-sm font-bold text-white shadow-lg shadow-emerald-950/30 transition hover:brightness-110 active:scale-[0.98] disabled:opacity-60"
                >
                    <LocateFixed
                        class="size-5 {locating ? 'animate-spin' : ''}"
                        aria-hidden="true"
                    />
                    {locating ? t('places.locating') : t('places.nearMe')}
                </button>
            </div>

            {#if hasCoords}
                <div
                    class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-2.5"
                >
                    <p class="text-sm font-bold text-emerald-100">
                        {t('places.sortedByDistance')}
                    </p>
                    <button
                        type="button"
                        onclick={clearLocation}
                        class="inline-flex min-h-9 items-center gap-1 rounded-full px-3 text-sm font-bold text-emerald-200 transition hover:bg-white/10"
                    >
                        <X class="size-4" aria-hidden="true" />
                        {t('common.clear')}
                    </button>
                </div>
            {/if}
        </div>
    </section>

    <div class="mx-auto -mt-8 w-full max-w-6xl px-4 md:px-6">
        <div
            class="rounded-[20px] bg-card p-5 shadow-lg shadow-slate-900/5 ring-1 ring-border"
        >
            <h2 class="text-xs font-bold text-muted-foreground">
                {t('places.byCategory')}
            </h2>
            <div class="mt-2 flex flex-wrap gap-2">
                <button
                    type="button"
                    onclick={() => (activeCategory = 'all')}
                    class="{chipBase} {activeCategory === 'all'
                        ? 'bg-emerald-600 text-white ring-emerald-600'
                        : 'bg-card text-secondary-foreground ring-border hover:ring-emerald-300'}"
                >
                    {t('common.all')}
                </button>
                {#each categories as [key, meta] (key)}
                    <button
                        type="button"
                        onclick={() => (activeCategory = key)}
                        class="{chipBase} inline-flex items-center gap-1.5 {activeCategory ===
                        key
                            ? 'bg-emerald-600 text-white ring-emerald-600'
                            : 'bg-card text-secondary-foreground ring-border hover:ring-emerald-300'}"
                    >
                        <span aria-hidden="true">{meta.icon}</span>
                        {t(`categories.${key}`)}
                    </button>
                {/each}
            </div>

            <h2 class="mt-5 text-xs font-bold text-muted-foreground">
                {t('places.byCity')}
            </h2>
            <div class="mt-2 flex flex-wrap gap-2">
                <button
                    type="button"
                    onclick={() => chooseCity('all')}
                    class="{chipBase} {activeCity === 'all'
                        ? 'bg-[#0b1e33] text-white ring-[#0b1e33]'
                        : 'bg-card text-secondary-foreground ring-border hover:ring-ring'}"
                >
                    {t('common.allCities')}
                </button>
                {#each cities as city (city.id)}
                    <button
                        type="button"
                        onclick={() => chooseCity(city.id)}
                        class="{chipBase} {activeCity === city.id
                            ? 'bg-[#0b1e33] text-white ring-[#0b1e33]'
                            : 'bg-card text-secondary-foreground ring-border hover:ring-ring'}"
                    >
                        {cityName(city)}
                    </button>
                {/each}
            </div>
        </div>
    </div>

    <div class="mx-auto mt-8 w-full max-w-6xl px-4 pb-20 md:px-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm font-bold text-muted-foreground">
                {t('places.count', { count: formatNumber(results.length) })}
            </p>

            <div
                class="inline-flex shrink-0 rounded-full bg-muted p-1 ring-1 ring-border"
                role="group"
                aria-label={t('places.title')}
            >
                <button
                    type="button"
                    onclick={() => (view = 'list')}
                    aria-pressed={view === 'list'}
                    class="inline-flex min-h-9 items-center gap-1.5 rounded-full px-4 text-sm font-bold transition {view ===
                    'list'
                        ? 'bg-card text-foreground shadow-sm'
                        : 'text-muted-foreground hover:text-foreground'}"
                >
                    <List class="size-4" aria-hidden="true" />
                    {t('places.viewList')}
                </button>
                <button
                    type="button"
                    onclick={() => (view = 'map')}
                    aria-pressed={view === 'map'}
                    class="inline-flex min-h-9 items-center gap-1.5 rounded-full px-4 text-sm font-bold transition {view ===
                    'map'
                        ? 'bg-card text-foreground shadow-sm'
                        : 'text-muted-foreground hover:text-foreground'}"
                >
                    <Map class="size-4" aria-hidden="true" />
                    {t('places.viewMap')}
                </button>
            </div>
        </div>

        {#if locating}
            <div
                class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
                aria-busy="true"
                aria-live="polite"
            >
                {#each Array.from({ length: 6 }) as _, index (index)}
                    <PlaceCardSkeleton />
                {/each}
            </div>
        {:else if results.length === 0}
            <div
                class="mt-4 rounded-[20px] border-2 border-dashed border-border bg-card p-12 text-center"
            >
                <p class="text-4xl" aria-hidden="true">🔍</p>
                <p class="mt-2 text-lg font-bold text-foreground">
                    {t('places.noResults')}
                </p>
                <p class="mt-1 text-base text-muted-foreground">
                    {t('places.noResultsHint')}
                </p>
            </div>
        {:else if view === 'map'}
            <div class="mt-4">
                <PlacesMap places={filtered} onSelect={openPlaceSheet} />
            </div>
        {:else}
            <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                {#each results as row (row.place.id)}
                    <PlaceShowcaseCard
                        place={row.place}
                        distanceKm={hasCoords ? row.distanceKm : null}
                    />
                {/each}
            </div>
        {/if}
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
