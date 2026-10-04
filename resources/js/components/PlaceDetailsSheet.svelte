<script lang="ts">
    import Accessibility from '@lucide/svelte/icons/accessibility';
    import CalendarClock from '@lucide/svelte/icons/calendar-clock';
    import CalendarX from '@lucide/svelte/icons/calendar-x';
    import Check from '@lucide/svelte/icons/check';
    import Clock from '@lucide/svelte/icons/clock';
    import DoorOpen from '@lucide/svelte/icons/door-open';
    import ExternalLink from '@lucide/svelte/icons/external-link';
    import Heart from '@lucide/svelte/icons/heart';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import MoonStar from '@lucide/svelte/icons/moon-star';
    import Navigation from '@lucide/svelte/icons/navigation';
    import Plus from '@lucide/svelte/icons/plus';
    import Star from '@lucide/svelte/icons/star';
    import Ticket from '@lucide/svelte/icons/ticket';
    import Trees from '@lucide/svelte/icons/trees';
    import Users from '@lucide/svelte/icons/users';
    import X from '@lucide/svelte/icons/x';
    import { untrack } from 'svelte';
    import { toast } from 'svelte-sonner';
    import MiniMap from '@/components/MiniMap.svelte';
    import PlaceMedia from '@/components/PlaceMedia.svelte';
    import TrustBadges from '@/components/TrustBadges.svelte';
    import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet';
    import { isFavorite, toggleFavorite } from '@/lib/favorites.svelte';
    import { openStreetMapUrl } from '@/lib/geo';
    import {
        formatFullDate,
        formatHijriDate,
        formatNumber,
        t,
    } from '@/lib/i18n.svelte';
    import { cityName, placeDescription, placeName } from '@/lib/localize';
    import {
        loadPrayerTimes,
        prayerTimesFailed,
        prayerTimesFor,
    } from '@/lib/prayer-times.svelte';
    import { closePlaceSheet, placeSheetState } from '@/lib/place-sheet.svelte';
    import { categoryMeta, cityById } from '@/lib/tourism.svelte';
    import { isInTripDraft, toggleTripDraft } from '@/lib/trip-draft.svelte';

    const state = placeSheetState();
    const place = $derived(state.place);
    const meta = $derived(place ? categoryMeta[place.category] : null);
    const city = $derived(place ? cityById(place.cityId) : undefined);
    const saved = $derived(place ? isFavorite(place.id) : false);
    const inTrip = $derived(place ? isInTripDraft(place.id) : false);
    const prayerTimes = $derived(place ? prayerTimesFor(place.cityId) : null);
    const prayerFailed = $derived(
        place ? prayerTimesFailed(place.cityId) : false,
    );

    $effect(() => {
        const cityId = place?.cityId;

        if (cityId) {
            untrack(() => void loadPrayerTimes(cityId));
        }
    });

    const prayers = $derived([
        { key: 'fajr', label: t('prayer.fajr'), time: prayerTimes?.fajr },
        { key: 'dhuhr', label: t('prayer.dhuhr'), time: prayerTimes?.dhuhr },
        { key: 'asr', label: t('prayer.asr'), time: prayerTimes?.asr },
        {
            key: 'maghrib',
            label: t('prayer.maghrib'),
            time: prayerTimes?.maghrib,
        },
        { key: 'isha', label: t('prayer.isha'), time: prayerTimes?.isha },
    ]);

    function priceLabel(): string {
        if (!place) {
            return '';
        }

        if (place.ticketPrice === null) {
            return t('common.perRequest');
        }

        if (place.ticketPrice === 0) {
            return t('common.free');
        }

        return `${formatNumber(place.ticketPrice)} ${t('common.currency')}`;
    }

    function onSave(): void {
        if (!place) {
            return;
        }

        const nowSaved = toggleFavorite(place.id);
        toast.success(nowSaved ? t('common.saved') : t('common.save'));
    }

    function onAdd(): void {
        if (!place) {
            return;
        }

        const nowAdded = toggleTripDraft(place.id);
        toast.success(
            nowAdded ? t('common.addedToTrip') : t('common.addToTrip'),
        );
    }

    const actionClass =
        'inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl px-4 text-sm font-bold transition active:scale-[0.98]';
</script>

<Sheet
    open={place !== null}
    onOpenChange={(value) => {
        if (!value) {
            closePlaceSheet();
        }
    }}
>
    <SheetContent
        side="bottom"
        showCloseButton={false}
        class="max-h-[92dvh] gap-0 overflow-y-auto rounded-t-[28px] bg-card p-0"
    >
        {#if place && meta}
            <SheetTitle class="sr-only">{placeName(place)}</SheetTitle>

            <div class="relative">
                <PlaceMedia
                    {place}
                    class="h-52 w-full sm:h-64"
                    overlay
                    eager
                >
                    <button
                        type="button"
                        onclick={closePlaceSheet}
                        class="absolute end-3 top-3 flex size-11 items-center justify-center rounded-full bg-black/40 text-white backdrop-blur transition hover:bg-black/60"
                        aria-label={t('common.close')}
                        title={t('common.close')}
                    >
                        <X class="size-5" aria-hidden="true" />
                    </button>
                    <div
                        class="absolute inset-x-4 bottom-3 flex items-end justify-between gap-3"
                    >
                        <TrustBadges {place} tone="overlay" />
                    </div>
                </PlaceMedia>
            </div>

            <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-5 pb-8 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-2xl font-bold text-foreground">
                            {placeName(place)}
                        </h2>
                        <p class="mt-0.5 text-base text-muted-foreground">
                            {place.nameEn}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-950/50 px-3 py-1 text-sm font-bold text-emerald-700 dark:text-emerald-400"
                        >
                            <span aria-hidden="true">{meta.icon}</span>
                            {t(`categories.${place.category}`)}
                        </span>
                        <span
                            class="inline-flex items-center gap-1 rounded-full bg-muted px-3 py-1 text-sm font-bold text-secondary-foreground"
                        >
                            <Star class="size-4 fill-amber-400 text-amber-400" />
                            {formatNumber(place.rating)}
                        </span>
                    </div>
                </div>

                <p class="flex items-center gap-1.5 text-base text-muted-foreground">
                    <MapPin class="size-5 shrink-0" aria-hidden="true" />
                    {#if city}
                        {cityName(city)} · {city.region}
                    {/if}
                </p>

                <p class="text-base leading-relaxed text-secondary-foreground">
                    {placeDescription(place)}
                </p>

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                    <a
                        href={openStreetMapUrl(place)}
                        target="_blank"
                        rel="noopener noreferrer"
                        class="{actionClass} bg-gradient-to-br from-emerald-400 to-teal-700 text-white shadow-lg shadow-emerald-900/20 hover:brightness-110"
                    >
                        <Navigation class="size-5" aria-hidden="true" />
                        {t('place.directions')}
                    </a>
                    <button
                        type="button"
                        onclick={onSave}
                        class="{actionClass} {saved
                            ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-800'
                            : 'bg-card text-secondary-foreground ring-1 ring-border hover:ring-emerald-300'}"
                    >
                        {#if saved}
                            <Check class="size-5" aria-hidden="true" />
                        {:else}
                            <Heart class="size-5" aria-hidden="true" />
                        {/if}
                        {saved ? t('common.saved') : t('common.save')}
                    </button>
                    <button
                        type="button"
                        onclick={onAdd}
                        class="{actionClass} {inTrip
                            ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-800'
                            : 'bg-card text-secondary-foreground ring-1 ring-border hover:ring-emerald-300'}"
                    >
                        {#if inTrip}
                            <Check class="size-5" aria-hidden="true" />
                        {:else}
                            <Plus class="size-5" aria-hidden="true" />
                        {/if}
                        {inTrip ? t('common.addedToTrip') : t('common.addToTrip')}
                    </button>
                </div>

                <div class="flex flex-col gap-2">
                    <MiniMap places={[place]} />
                    <a
                        href={openStreetMapUrl(place)}
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 self-start text-sm font-bold text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300"
                    >
                        <ExternalLink class="size-4" aria-hidden="true" />
                        {t('place.openInMaps')}
                    </a>
                </div>

                <section class="flex flex-col gap-3">
                    <h3 class="text-lg font-bold text-foreground">
                        {t('place.practical')}
                    </h3>
                    <ul class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <li
                            class="flex items-center gap-3 rounded-xl bg-muted/60 p-3 ring-1 ring-border"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            >
                                <Clock class="size-5" aria-hidden="true" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm text-muted-foreground">
                                    {t('place.openingHours')}
                                </span>
                                <span class="block font-bold text-foreground">
                                    {place.openingHours ?? '—'}
                                </span>
                            </span>
                        </li>
                        <li
                            class="flex items-center gap-3 rounded-xl bg-muted/60 p-3 ring-1 ring-border"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            >
                                <CalendarClock class="size-5" aria-hidden="true" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm text-muted-foreground">
                                    {t('place.duration')}
                                </span>
                                <span class="block font-bold text-foreground">
                                    {t('place.durationMinutes', {
                                        count: formatNumber(
                                            place.avgVisitDuration,
                                        ),
                                    })}
                                </span>
                            </span>
                        </li>
                        <li
                            class="flex items-center gap-3 rounded-xl bg-muted/60 p-3 ring-1 ring-border"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            >
                                <Ticket class="size-5" aria-hidden="true" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm text-muted-foreground">
                                    {t('place.ticket')}
                                </span>
                                <span class="block font-bold text-foreground">
                                    {priceLabel()}
                                </span>
                            </span>
                        </li>
                        <li
                            class="flex items-center gap-3 rounded-xl bg-muted/60 p-3 ring-1 ring-border"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            >
                                <Clock class="size-5" aria-hidden="true" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm text-muted-foreground">
                                    {t('place.bestTime')}
                                </span>
                                <span class="block font-bold text-foreground">
                                    {t(`time.${place.bestTime}`)}
                                </span>
                            </span>
                        </li>
                        <li
                            class="flex items-center gap-3 rounded-xl bg-muted/60 p-3 ring-1 ring-border"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            >
                                <MoonStar class="size-5" aria-hidden="true" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm text-muted-foreground">
                                    {t('place.prayerRoom')}
                                </span>
                                <span class="block font-bold text-foreground">
                                    {place.hasPrayerFacilities
                                        ? t('place.available')
                                        : t('place.notAvailable')}
                                </span>
                            </span>
                        </li>
                        <li
                            class="flex items-center gap-3 rounded-xl bg-muted/60 p-3 ring-1 ring-border"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            >
                                <CalendarX class="size-5" aria-hidden="true" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm text-muted-foreground">
                                    {t('place.friday')}
                                </span>
                                <span class="block font-bold text-foreground">
                                    {place.closedFriday
                                        ? t('place.closed')
                                        : t('place.open')}
                                </span>
                            </span>
                        </li>
                        <li
                            class="flex items-center gap-3 rounded-xl bg-muted/60 p-3 ring-1 ring-border"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            >
                                <Accessibility class="size-5" aria-hidden="true" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm text-muted-foreground">
                                    {t('badges.wheelchair')}
                                </span>
                                <span class="block font-bold text-foreground">
                                    {place.wheelchairAccessible
                                        ? t('place.available')
                                        : t('place.notAvailable')}
                                </span>
                            </span>
                        </li>
                        <li
                            class="flex items-center gap-3 rounded-xl bg-muted/60 p-3 ring-1 ring-border"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            >
                                {#if place.isIndoor}
                                    <DoorOpen class="size-5" aria-hidden="true" />
                                {:else}
                                    <Trees class="size-5" aria-hidden="true" />
                                {/if}
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm text-muted-foreground">
                                    {t('place.setting')}
                                </span>
                                <span class="block font-bold text-foreground">
                                    {place.isIndoor
                                        ? t('place.indoor')
                                        : t('place.outdoor')}
                                </span>
                            </span>
                        </li>
                        <li
                            class="flex items-center gap-3 rounded-xl bg-muted/60 p-3 ring-1 ring-border sm:col-span-2"
                        >
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-900/60"
                            >
                                <Users class="size-5" aria-hidden="true" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm text-muted-foreground">
                                    {t('common.family')}
                                </span>
                                <span class="block font-bold text-foreground">
                                    {place.familyFriendly
                                        ? t('place.suitable')
                                        : t('place.notSuitable')}
                                </span>
                            </span>
                        </li>
                    </ul>
                </section>

                {#if !prayerFailed}
                    <section class="flex flex-col gap-3">
                        <div class="flex flex-wrap items-baseline justify-between gap-1">
                            <h3 class="text-lg font-bold text-foreground">
                                {t('place.prayerTimes')}
                            </h3>
                            <p class="text-sm text-muted-foreground">
                                {formatFullDate()}
                            </p>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {formatHijriDate()}
                        </p>
                        {#if prayerTimes}
                            <ul class="grid grid-cols-5 gap-2">
                                {#each prayers as prayer (prayer.key)}
                                    <li
                                        class="flex flex-col items-center gap-1 rounded-xl bg-muted/60 px-1 py-3 text-center ring-1 ring-border"
                                    >
                                        <span
                                            class="text-xs font-bold text-muted-foreground"
                                        >
                                            {prayer.label}
                                        </span>
                                        <span
                                            class="text-base font-bold text-foreground"
                                        >
                                            {prayer.time}
                                        </span>
                                    </li>
                                {/each}
                            </ul>
                        {:else}
                            <ul
                                class="grid grid-cols-5 gap-2"
                                aria-busy="true"
                                aria-label={t('place.prayerTimes')}
                            >
                                {#each Array.from({ length: 5 }) as _, index (index)}
                                    <li
                                        class="flex flex-col items-center gap-1 rounded-xl bg-muted/60 px-1 py-3 text-center ring-1 ring-border"
                                    >
                                        <span
                                            class="h-3 w-8 animate-pulse rounded-full bg-muted-foreground/20"
                                        ></span>
                                        <span
                                            class="h-4 w-10 animate-pulse rounded-full bg-muted-foreground/20"
                                        ></span>
                                    </li>
                                {/each}
                            </ul>
                        {/if}
                        <p class="text-xs text-muted-foreground">
                            {t('place.prayerHint')}
                        </p>
                    </section>
                {/if}

                {#if place.tags.length > 0}
                    <section class="flex flex-col gap-2">
                        <h3 class="text-lg font-bold text-foreground">
                            {t('place.tags')}
                        </h3>
                        <ul class="flex flex-wrap gap-2">
                            {#each place.tags as tag (tag)}
                                <li
                                    class="rounded-full bg-muted px-3 py-1 text-sm font-bold text-secondary-foreground"
                                >
                                    {tag}
                                </li>
                            {/each}
                        </ul>
                    </section>
                {/if}
            </div>
        {/if}
    </SheetContent>
</Sheet>
