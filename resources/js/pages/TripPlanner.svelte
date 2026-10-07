<script lang="ts">
    import { Link, router, useHttp } from '@inertiajs/svelte';
    import CalendarDays from '@lucide/svelte/icons/calendar-days';
    import Check from '@lucide/svelte/icons/check';
    import ChevronDown from '@lucide/svelte/icons/chevron-down';
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import ChevronUp from '@lucide/svelte/icons/chevron-up';
    import Circle from '@lucide/svelte/icons/circle';
    import Copy from '@lucide/svelte/icons/copy';
    import Link2Off from '@lucide/svelte/icons/link-2-off';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Minus from '@lucide/svelte/icons/minus';
    import Plus from '@lucide/svelte/icons/plus';
    import Printer from '@lucide/svelte/icons/printer';
    import RotateCcw from '@lucide/svelte/icons/rotate-ccw';
    import Share2 from '@lucide/svelte/icons/share-2';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Star from '@lucide/svelte/icons/star';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import X from '@lucide/svelte/icons/x';
    import { untrack } from 'svelte';
    import { toast } from 'svelte-sonner';
    import AppHead from '@/components/AppHead.svelte';
    import SiteHeader from '@/components/SiteHeader.svelte';
    import { Toaster } from '@/components/ui/sonner';
    import { selectedCityId } from '@/lib/city.svelte';
    import {
        formatDate,
        formatList,
        formatNumber,
        getLocale,
        t,
    } from '@/lib/i18n.svelte';
    import { cityName, placeName } from '@/lib/localize';
    import {
        categoryMeta,
        cities,
        cityById,
        placeById,
        places as allPlaces,
        placesByCity,
    } from '@/lib/tourism.svelte';
    import {
        clearTripDraft,
        tripDraftIds,
        toggleTripDraft,
    } from '@/lib/trip-draft.svelte';
    import {
        print as printTrip,
        share as shareTrip,
        store as storeTrip,
        unshare as unshareTrip,
    } from '@/routes/trips';
    import { store as storeDayItem } from '@/routes/trip-days/items';
    import {
        destroy as destroyItem,
        move as moveItem,
        toggle as toggleVisited,
        update as updateItem,
    } from '@/routes/trip-items';
    import type { City, Place, PlaceCategory } from '@/types';

    interface PlanEntry {
        place: Place;
        time: string;
        minutes: number;
        itemId: number | null;
        done: boolean;
    }

    interface PlanDay {
        day: number;
        date: string;
        cityId: number | null;
        tripDayId: number | null;
        entries: PlanEntry[];
    }

    interface SavedTripItem {
        id: number;
        place_id: number | null;
        title: string;
        start_time: string | null;
        duration_minutes: number | null;
        completed: boolean;
    }

    interface SavedTripDay {
        id: number;
        day_number: number;
        date: string;
        city_id: number | null;
        items: SavedTripItem[];
    }

    interface SavedTrip {
        id: number;
        title: string;
        city_id: number | null;
        city_ids: number[];
        city_name: string | null;
        city_name_en: string | null;
        start_date: string;
        end_date: string;
        days_count: number;
        travelers_count: number;
        budget: string | null;
        interests: string[];
        items_count: number;
        completed_count: number;
        days: SavedTripDay[];
        share_token: string | null;
        is_public: boolean;
        share_url: string | null;
    }

    let {
        savedTrip = null,
    }: {
        savedTrip?: SavedTrip | null;
    } = $props();

    const initialSavedTrip = untrack(() => savedTrip);

    let shareState = $state<{
        id: number;
        isPublic: boolean;
        url: string | null;
    } | null>(
        initialSavedTrip
            ? {
                  id: initialSavedTrip.id,
                  isPublic: initialSavedTrip.is_public,
                  url: initialSavedTrip.share_url,
              }
            : null,
    );

    const todayIso = new Date().toISOString().slice(0, 10);
    const budgetPresets = [1000, 2000, 3000, 5000, 8000];
    const categories = Object.entries(categoryMeta) as [
        PlaceCategory,
        (typeof categoryMeta)[PlaceCategory],
    ][];

    let step = $state(0);
    let cityIds = $state<number[]>(
        initialSavedTrip?.city_ids?.length
            ? initialSavedTrip.city_ids
            : [selectedCityId()],
    );
    let startDate = $state(initialSavedTrip?.start_date ?? todayIso);
    let days = $state(initialSavedTrip?.days_count ?? 3);
    let travelers = $state(initialSavedTrip?.travelers_count ?? 2);
    let budget = $state(
        initialSavedTrip?.budget ? Number(initialSavedTrip.budget) : 3000,
    );
    let interests = $state<PlaceCategory[]>(
        (initialSavedTrip?.interests ?? []) as PlaceCategory[],
    );
    let generated = $state(
        Boolean(initialSavedTrip && initialSavedTrip.days.length > 0),
    );
    let plan = $state<PlanDay[]>(
        initialSavedTrip ? restorePlan(initialSavedTrip) : [],
    );
    let restoredNotice = $state(
        Boolean(initialSavedTrip && initialSavedTrip.days.length > 0),
    );

    const steps = $derived([
        { title: t('trips.stepCity'), hint: t('trips.pickCityHint') },
        { title: t('trips.stepDate'), hint: t('trips.pickDateHint') },
        { title: t('trips.stepDays'), hint: t('trips.pickDaysHint') },
        { title: t('trips.stepTravelers'), hint: t('trips.pickTravelersHint') },
        { title: t('trips.stepBudget'), hint: t('trips.pickBudgetHint') },
        { title: t('trips.stepInterests'), hint: t('trips.pickInterestsHint') },
    ]);

    const selectedCities = $derived(
        cities.filter((city) => cityIds.includes(city.id)),
    );
    const isLastStep = $derived(step === steps.length - 1);
    const progress = $derived(Math.round(((step + 1) / steps.length) * 100));

    const estimated = $derived.by(() => {
        if (!generated) {
            return 0;
        }

        const tickets = plan
            .flatMap((day) => day.entries)
            .reduce((sum, entry) => sum + (entry.place.ticketPrice ?? 0), 0);
        const food = days * travelers * 120;
        const intercityTrips = plan.filter(
            (day, index) =>
                index > 0 &&
                day.cityId !== null &&
                day.cityId !== plan[index - 1].cityId,
        ).length;
        const transport = plan.length * 150 + intercityTrips * 350;

        return tickets + food + transport;
    });

    const draftPlaces = $derived(
        tripDraftIds()
            .map((id) => allPlaces.find((place) => place.id === id))
            .filter((place): place is Place => Boolean(place)),
    );

    function formatTime(minutes: number): string {
        const date = new Date();
        date.setHours(Math.floor(minutes / 60), minutes % 60, 0, 0);

        return new Intl.DateTimeFormat(getLocale(), {
            hour: 'numeric',
            minute: '2-digit',
        }).format(date);
    }

    function minutesFromTime(value: string | null): number {
        if (!value) {
            return 9 * 60;
        }

        const [hours, mins] = value.split(':').map(Number);

        return (hours || 0) * 60 + (mins || 0);
    }

    function toIsoTime(minutes: number): string {
        const hours = Math.floor(minutes / 60)
            .toString()
            .padStart(2, '0');
        const mins = (minutes % 60).toString().padStart(2, '0');

        return `${hours}:${mins}`;
    }

    function restorePlan(trip: SavedTrip): PlanDay[] {
        return trip.days.map((day) => {
            const inferredCityId =
                day.city_id ??
                day.items
                    .map((item) =>
                        item.place_id === null
                            ? undefined
                            : placeById(item.place_id),
                    )
                    .find((place) => place !== undefined)?.cityId ??
                null;

            return {
                day: day.day_number,
                date: day.date,
                cityId: inferredCityId,
                tripDayId: day.id,
                entries: day.items
                    .map((item): PlanEntry | null => {
                        const place = item.place_id
                            ? placeById(item.place_id)
                            : undefined;

                        if (!place) {
                            return null;
                        }

                        const minutes = minutesFromTime(item.start_time);

                        return {
                            place,
                            time: formatTime(minutes),
                            minutes,
                            itemId: item.id,
                            done: item.completed,
                        };
                    })
                    .filter((entry): entry is PlanEntry => entry !== null),
            };
        });
    }

    function savePlan(result: PlanDay[]): void {
        router.post(
            storeTrip().url,
            {
                city_ids: cityIds,
                start_date: startDate,
                days,
                travelers,
                budget,
                interests,
                plan: result.map((day) => ({
                    day: day.day,
                    date: day.date,
                    city_id: day.cityId,
                    entries: day.entries.map((entry) => ({
                        place_id: entry.place.id,
                        time: toIsoTime(entry.minutes),
                        duration: entry.place.avgVisitDuration || 60,
                    })),
                })),
            },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: (page) => {
                    const saved = (
                        page.props as { savedTrip?: SavedTrip | null }
                    ).savedTrip;

                    if (saved) {
                        plan = plan.map((day) => {
                            const savedDay = saved.days.find(
                                (item) => item.day_number === day.day,
                            );

                            if (!savedDay) {
                                return day;
                            }

                            return {
                                ...day,
                                tripDayId: savedDay.id,
                                entries: day.entries.map((entry, index) => ({
                                    ...entry,
                                    itemId: savedDay.items[index]?.id ?? null,
                                })),
                            };
                        });

                        shareState = {
                            id: saved.id,
                            isPublic: saved.is_public,
                            url: saved.share_url,
                        };
                    }

                    toast.success(t('trips.saved'));
                },
                onError: () => toast.error(t('trips.saveFailed')),
            },
        );
    }

    const http = useHttp({ completed: false });
    const shareHttp = useHttp<
        Record<string, never>,
        { share_url?: string; share_token?: string; is_public: boolean }
    >({});

    function toggleDone(entry: PlanEntry): void {
        if (entry.itemId === null) {
            return;
        }

        const next = !entry.done;
        const itemId = entry.itemId;

        plan = plan.map((day) => ({
            ...day,
            entries: day.entries.map((item) =>
                item.itemId === itemId ? { ...item, done: next } : item,
            ),
        }));

        http.completed = next;
        http.patch(toggleVisited.url(itemId), {
            onError: () => {
                plan = plan.map((day) => ({
                    ...day,
                    entries: day.entries.map((item) =>
                        item.itemId === itemId ? { ...item, done: !next } : item,
                    ),
                }));
                toast.error(t('trips.saveFailed'));
            },
        });
    }

    const addItemHttp = useHttp<
        {
            place_id: number | null;
            start_time: string;
            duration_minutes: number | null;
        },
        { id: number }
    >({ place_id: null, start_time: '09:00', duration_minutes: null });
    const updateItemHttp = useHttp<
        { start_time: string },
        { id: number; start_time: string | null }
    >({ start_time: '09:00' });
    const deleteItemHttp = useHttp<Record<string, never>, { deleted: boolean }>(
        {},
    );
    const moveItemHttp = useHttp<
        { direction: 'up' | 'down' },
        { moved: boolean }
    >({ direction: 'up' });

    function mergeEntry(
        dayIndex: number,
        entryIndex: number,
        changes: Partial<PlanEntry>,
    ): void {
        plan = plan.map((day, index) =>
            index === dayIndex
                ? {
                      ...day,
                      entries: day.entries.map((entry, position) =>
                          position === entryIndex
                              ? { ...entry, ...changes }
                              : entry,
                      ),
                  }
                : day,
        );
    }

    function insertEntry(
        dayIndex: number,
        entryIndex: number,
        entry: PlanEntry,
    ): void {
        plan = plan.map((day, index) => {
            if (index !== dayIndex) {
                return day;
            }

            const entries = [...day.entries];
            entries.splice(Math.min(entryIndex, entries.length), 0, entry);

            return { ...day, entries };
        });
    }

    function removeEntry(dayIndex: number, entryIndex: number): void {
        const entry = plan[dayIndex]?.entries[entryIndex];

        if (!entry) {
            return;
        }

        plan = plan.map((day, index) =>
            index === dayIndex
                ? {
                      ...day,
                      entries: day.entries.filter(
                          (_, position) => position !== entryIndex,
                      ),
                  }
                : day,
        );

        if (entry.itemId === null) {
            return;
        }

        const revert = (): void => {
            insertEntry(dayIndex, entryIndex, entry);
            toast.error(t('trips.editFailed'));
        };

        deleteItemHttp
            .delete(destroyItem.url(entry.itemId), {
                onSuccess: () => toast.success(t('trips.itemRemoved')),
                onError: revert,
                onHttpException: revert,
                onNetworkError: revert,
            })
            .catch(revert);
    }

    function moveEntry(
        dayIndex: number,
        entryIndex: number,
        direction: 'up' | 'down',
    ): void {
        const day = plan[dayIndex];
        const entry = day?.entries[entryIndex];

        if (!day || !entry) {
            return;
        }

        const target = direction === 'up' ? entryIndex - 1 : entryIndex + 1;

        if (target < 0 || target >= day.entries.length) {
            return;
        }

        const entries = [...day.entries];
        [entries[entryIndex], entries[target]] = [
            entries[target],
            entries[entryIndex],
        ];
        plan = plan.map((current, index) =>
            index === dayIndex ? { ...current, entries } : current,
        );

        if (entry.itemId === null) {
            return;
        }

        const revert = (): void => {
            const reverted = [...entries];
            [reverted[target], reverted[entryIndex]] = [
                reverted[entryIndex],
                reverted[target],
            ];
            plan = plan.map((current, index) =>
                index === dayIndex
                    ? { ...current, entries: reverted }
                    : current,
            );
            toast.error(t('trips.editFailed'));
        };

        moveItemHttp.direction = direction;
        moveItemHttp
            .post(moveItem.url(entry.itemId), {
                onError: revert,
                onHttpException: revert,
                onNetworkError: revert,
            })
            .catch(revert);
    }

    function updateEntryTime(
        dayIndex: number,
        entryIndex: number,
        value: string,
    ): void {
        const entry = plan[dayIndex]?.entries[entryIndex];

        if (!entry || value === '') {
            return;
        }

        const minutes = minutesFromTime(value);
        const previous = { minutes: entry.minutes, time: entry.time };

        mergeEntry(dayIndex, entryIndex, {
            minutes,
            time: formatTime(minutes),
        });

        if (entry.itemId === null) {
            return;
        }

        const revert = (): void => {
            mergeEntry(dayIndex, entryIndex, previous);
            toast.error(t('trips.editFailed'));
        };

        updateItemHttp.start_time = toIsoTime(minutes);
        updateItemHttp
            .put(updateItem.url(entry.itemId), {
                onError: revert,
                onHttpException: revert,
                onNetworkError: revert,
            })
            .catch(revert);
    }

    function availablePlaces(day: PlanDay): Place[] {
        if (day.cityId === null) {
            return [];
        }

        const used = new Set(day.entries.map((entry) => entry.place.id));

        return placesByCity(day.cityId).filter((place) => !used.has(place.id));
    }

    function nextEntryMinutes(day: PlanDay): number {
        const last = day.entries[day.entries.length - 1];

        return last
            ? last.minutes + (last.place.avgVisitDuration || 60) + 45
            : 9 * 60;
    }

    function addEntry(dayIndex: number, placeId: number): void {
        const day = plan[dayIndex];
        const place = placeId === 0 ? undefined : placeById(placeId);

        if (!day || !place) {
            return;
        }

        if (day.entries.some((entry) => entry.place.id === place.id)) {
            return;
        }

        const minutes = nextEntryMinutes(day);
        const entryIndex = day.entries.length;

        insertEntry(dayIndex, entryIndex, {
            place,
            time: formatTime(minutes),
            minutes,
            itemId: null,
            done: false,
        });

        if (day.tripDayId === null) {
            toast.success(t('trips.itemAdded'));

            return;
        }

        const revert = (): void => {
            removeEntry(dayIndex, entryIndex);
            toast.error(t('trips.editFailed'));
        };

        addItemHttp.place_id = place.id;
        addItemHttp.start_time = toIsoTime(minutes);
        addItemHttp.duration_minutes = place.avgVisitDuration || 60;
        addItemHttp
            .post(storeDayItem.url(day.tripDayId), {
                onSuccess: (response) => {
                    if (typeof response?.id === 'number') {
                        mergeEntry(dayIndex, entryIndex, {
                            itemId: response.id,
                        });
                    }

                    toast.success(t('trips.itemAdded'));
                },
                onError: revert,
                onHttpException: revert,
                onNetworkError: revert,
            })
            .catch(revert);
    }

    async function copyText(value: string): Promise<boolean> {
        try {
            await navigator.clipboard.writeText(value);

            return true;
        } catch {
            return false;
        }
    }

    function sharePlan(): void {
        if (!shareState) {
            return;
        }

        const id = shareState.id;

        shareHttp
            .post(shareTrip.url(id), {
                onSuccess: async (response) => {
                    const url = response.share_url ?? null;

                    shareState = { id, isPublic: response.is_public, url };

                    if (url !== null && !(await copyText(url))) {
                        toast.error(t('trips.shareFailed'));

                        return;
                    }

                    toast.success(t('trips.linkCopied'));
                },
                onError: () => {
                    toast.error(t('trips.shareFailed'));
                },
                onHttpException: () => {
                    toast.error(t('trips.shareFailed'));
                },
                onNetworkError: () => {
                    toast.error(t('trips.shareFailed'));
                },
            })
            .catch(() => {
                toast.error(t('trips.shareFailed'));
            });
    }

    function stopSharing(): void {
        if (!shareState) {
            return;
        }

        const id = shareState.id;

        shareHttp
            .post(unshareTrip.url(id), {
                onSuccess: () => {
                    if (shareState) {
                        shareState = { ...shareState, isPublic: false };
                    }
                },
                onError: () => {
                    toast.error(t('trips.shareFailed'));
                },
                onHttpException: () => {
                    toast.error(t('trips.shareFailed'));
                },
                onNetworkError: () => {
                    toast.error(t('trips.shareFailed'));
                },
            })
            .catch(() => {
                toast.error(t('trips.shareFailed'));
            });
    }

    async function copyShareLink(): Promise<void> {
        const url = shareState?.url;

        if (!url) {
            return;
        }

        if (await copyText(url)) {
            toast.success(t('trips.linkCopied'));
        } else {
            toast.error(t('trips.shareFailed'));
        }
    }

    const totalPlaces = $derived(
        plan.flatMap((day) => day.entries).length,
    );
    const donePlaces = $derived(
        plan.flatMap((day) => day.entries).filter((entry) => entry.done)
            .length,
    );
    const progressPercent = $derived(
        totalPlaces > 0 ? Math.round((donePlaces / totalPlaces) * 100) : 0,
    );

    function addDays(iso: string, amount: number): string {
        const date = new Date(`${iso}T00:00:00`);
        date.setDate(date.getDate() + amount);

        return date.toISOString().slice(0, 10);
    }

    function toggleInterest(category: PlaceCategory): void {
        interests = interests.includes(category)
            ? interests.filter((value) => value !== category)
            : [...interests, category];
    }

    function toggleCity(id: number): void {
        if (cityIds.includes(id)) {
            if (cityIds.length === 1) {
                return;
            }

            cityIds = cityIds.filter((value) => value !== id);

            return;
        }

        cityIds = [...cityIds, id];
    }

    function distanceKm(a: City, b: City): number {
        const toRadians = (value: number): number => (value * Math.PI) / 180;
        const earthRadius = 6371;
        const deltaLat = toRadians(Number(b.latitude) - Number(a.latitude));
        const deltaLng = toRadians(Number(b.longitude) - Number(a.longitude));
        const haversine =
            Math.sin(deltaLat / 2) ** 2 +
            Math.cos(toRadians(Number(a.latitude))) *
                Math.cos(toRadians(Number(b.latitude))) *
                Math.sin(deltaLng / 2) ** 2;

        return 2 * earthRadius * Math.asin(Math.sqrt(haversine));
    }

    function orderCitiesByProximity(cityList: City[]): City[] {
        const remaining = [...cityList];
        const ordered: City[] = [];

        if (remaining.length === 0) {
            return ordered;
        }

        ordered.push(...remaining.splice(0, 1));

        while (remaining.length > 0) {
            const current = ordered[ordered.length - 1];
            let nearestIndex = 0;
            let nearestDistance = distanceKm(current, remaining[0]);

            for (let index = 1; index < remaining.length; index++) {
                const distance = distanceKm(current, remaining[index]);

                if (distance < nearestDistance) {
                    nearestIndex = index;
                    nearestDistance = distance;
                }
            }

            ordered.push(...remaining.splice(nearestIndex, 1));
        }

        return ordered;
    }

    function generate(): void {
        const selectedIds = new Set(selectedCities.map((city) => city.id));
        const extraDraftCities: City[] = [];

        for (const place of draftPlaces) {
            if (selectedIds.has(place.cityId)) {
                continue;
            }

            const city = cityById(place.cityId);

            if (city && !extraDraftCities.some((item) => item.id === city.id)) {
                extraDraftCities.push(city);
            }
        }

        const ordered = orderCitiesByProximity([
            ...extraDraftCities,
            ...selectedCities,
        ]);
        const base = Math.floor(days / ordered.length);
        const extra = days % ordered.length;
        const result: PlanDay[] = [];
        let dayNumber = 1;

        for (const [index, city] of ordered.entries()) {
            const allocatedDays = base + (index < extra ? 1 : 0);

            if (allocatedDays === 0) {
                continue;
            }

            const cityPlaces = placesByCity(city.id);
            const preferred =
                interests.length > 0
                    ? cityPlaces.filter((place) =>
                          interests.includes(place.category),
                      )
                    : cityPlaces;
            const pool: Place[] = [];
            const seen = new Set<number>();

            for (const place of [
                ...draftPlaces.filter((item) => item.cityId === city.id),
                ...(preferred.length > 0 ? preferred : cityPlaces),
            ]) {
                if (!seen.has(place.id)) {
                    seen.add(place.id);
                    pool.push(place);
                }
            }

            const perDay = Math.max(1, Math.ceil(pool.length / allocatedDays));
            let cursor = 0;

            for (let offset = 0; offset < allocatedDays; offset++) {
                const slice = pool.slice(cursor, cursor + perDay);
                cursor += perDay;

                let minutes = 9 * 60;
                const entries = slice.map((place) => {
                    const time = formatTime(minutes);
                    const entryMinutes = minutes;
                    minutes += (place.avgVisitDuration || 60) + 45;

                    return {
                        place,
                        time,
                        minutes: entryMinutes,
                        itemId: null,
                        done: false,
                    };
                });

                result.push({
                    day: dayNumber,
                    date: addDays(startDate, dayNumber - 1),
                    cityId: city.id,
                    tripDayId: null,
                    entries,
                });
                dayNumber += 1;
            }
        }

        plan = result;
        generated = true;
        restoredNotice = false;
        savePlan(result);
    }

    function restart(): void {
        generated = false;
        restoredNotice = false;
        step = 0;
    }

    function priceLabel(place: Place): string {
        if (place.ticketPrice === null) {
            return t('common.perRequest');
        }

        if (place.ticketPrice === 0) {
            return t('common.free');
        }

        return `${formatNumber(place.ticketPrice)} ${t('common.currency')}`;
    }

    const toggleChip =
        'flex min-h-14 items-center justify-center gap-2 rounded-xl px-4 text-sm font-bold ring-1 transition active:scale-[0.98]';
    const toggleChipOn =
        'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 ring-emerald-300 dark:ring-emerald-800';
    const toggleChipOff =
        'bg-card text-secondary-foreground ring-border hover:ring-emerald-300';
    const inputClass =
        'min-h-13 w-full rounded-xl bg-card px-4 text-base font-bold text-foreground outline-none ring-1 ring-border focus:ring-2 focus:ring-emerald-400';
    const roundButton =
        'flex size-14 items-center justify-center rounded-full bg-card text-secondary-foreground ring-1 ring-border transition hover:ring-emerald-400 active:scale-95';
</script>

<AppHead title={t('trips.title')} />

<SiteHeader active="trips" transparent />

<div class="min-h-dvh bg-background">
    <section
        class="bg-gradient-to-br from-[#16344f] via-[#0b2337] to-[#071523] pb-16 pt-28 text-white"
    >
        <div class="mx-auto w-full max-w-3xl px-4 md:px-6">
            <span
                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-emerald-200 ring-1 ring-white/15"
            >
                <Sparkles class="size-4" />
                {t('preview.formBadge')}
            </span>
            <h1 class="mt-4 text-3xl font-bold sm:text-4xl">
                {t('trips.title')}
            </h1>
            <p class="mt-2 text-slate-300">{t('trips.subtitle')}</p>
        </div>
    </section>

    <div class="mx-auto -mt-8 w-full max-w-3xl px-4 pb-20 md:px-6">
        {#if !generated}
            <div
                class="flex flex-col gap-5 rounded-[20px] bg-card p-6 shadow-lg shadow-slate-900/5 ring-1 ring-border"
            >
                <div class="flex flex-col gap-2">
                    <div
                        class="flex items-center justify-between text-xs font-bold text-muted-foreground"
                    >
                        <span>
                            {t('common.stepOf', {
                                current: formatNumber(step + 1),
                                total: formatNumber(steps.length),
                            })}
                        </span>
                        <span>{formatNumber(progress)}%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-gradient-to-l from-emerald-400 to-teal-700 transition-all"
                            style="width: {progress}%"
                        ></div>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-foreground">
                        {steps[step].title}
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {steps[step].hint}
                    </p>
                </div>

                {#if step === 0}
                    <div class="flex flex-col gap-3">
                        <div class="grid grid-cols-2 gap-3">
                            {#each cities as city (city.id)}
                                {@const on = cityIds.includes(city.id)}
                                <button
                                    type="button"
                                    onclick={() => toggleCity(city.id)}
                                    aria-pressed={on}
                                    class="{toggleChip} {on
                                        ? toggleChipOn
                                        : toggleChipOff}"
                                >
                                    {cityName(city)}
                                    {#if on}
                                        <Check class="size-5" />
                                    {/if}
                                </button>
                            {/each}
                        </div>
                        <p class="text-sm font-bold text-muted-foreground">
                            {t('trips.selectedCities', {
                                count: formatNumber(cityIds.length),
                            })}
                        </p>
                    </div>
                {:else if step === 1}
                    <label class="flex flex-col gap-2">
                        <span class="text-sm font-bold text-secondary-foreground">
                            {t('trips.startDate')}
                        </span>
                        <div class="relative">
                            <CalendarDays
                                class="pointer-events-none absolute inset-y-0 start-3 my-auto size-5 text-muted-foreground"
                            />
                            <input
                                type="date"
                                bind:value={startDate}
                                class="{inputClass} ps-11"
                            />
                        </div>
                    </label>
                {:else if step === 2}
                    <div class="flex items-center justify-center gap-5">
                        <button
                            type="button"
                            onclick={() => (days = Math.max(1, days - 1))}
                            class={roundButton}
                            aria-label={t('common.decrease')}
                        >
                            <Minus class="size-6" />
                        </button>
                        <span
                            class="min-w-24 text-center text-4xl font-bold text-foreground"
                        >
                            {formatNumber(days)}
                        </span>
                        <button
                            type="button"
                            onclick={() => (days = Math.min(14, days + 1))}
                            class={roundButton}
                            aria-label={t('common.increase')}
                        >
                            <Plus class="size-6" />
                        </button>
                    </div>
                {:else if step === 3}
                    <div class="flex items-center justify-center gap-5">
                        <button
                            type="button"
                            onclick={() =>
                                (travelers = Math.max(1, travelers - 1))}
                            class={roundButton}
                            aria-label={t('common.decrease')}
                        >
                            <Minus class="size-6" />
                        </button>
                        <span
                            class="min-w-24 text-center text-4xl font-bold text-foreground"
                        >
                            {formatNumber(travelers)}
                        </span>
                        <button
                            type="button"
                            onclick={() =>
                                (travelers = Math.min(30, travelers + 1))}
                            class={roundButton}
                            aria-label={t('common.increase')}
                        >
                            <Plus class="size-6" />
                        </button>
                    </div>
                {:else if step === 4}
                    <div class="flex flex-col gap-3">
                        <div class="flex flex-wrap gap-2">
                            {#each budgetPresets as preset (preset)}
                                <button
                                    type="button"
                                    onclick={() => (budget = preset)}
                                    class="min-h-11 rounded-full px-4 text-sm font-bold ring-1 transition {budget ===
                                    preset
                                        ? 'bg-emerald-600 text-white ring-emerald-600'
                                        : 'bg-card text-secondary-foreground ring-border hover:ring-emerald-300'}"
                                >
                                    {formatNumber(preset)}
                                    {t('common.currency')}
                                </button>
                            {/each}
                        </div>
                        <input
                            type="number"
                            min="0"
                            step="100"
                            bind:value={budget}
                            class={inputClass}
                        />
                    </div>
                {:else}
                    <div class="grid grid-cols-2 gap-3">
                        {#each categories as [key, meta] (key)}
                            {@const on = interests.includes(key)}
                            <button
                                type="button"
                                onclick={() => toggleInterest(key)}
                                aria-pressed={on}
                                class="{toggleChip} justify-start {on
                                    ? toggleChipOn
                                    : toggleChipOff}"
                            >
                                <span aria-hidden="true">{meta.icon}</span>
                                {t(`categories.${key}`)}
                                {#if on}
                                    <Check class="ms-auto size-5" />
                                {/if}
                            </button>
                        {/each}
                    </div>
                {/if}

                <div class="mt-1 flex items-center justify-between gap-3">
                    {#if step > 0}
                        <button
                            type="button"
                            onclick={() => (step = Math.max(0, step - 1))}
                            class="inline-flex min-h-11 items-center justify-center rounded-xl bg-card px-5 text-sm font-bold text-secondary-foreground ring-1 ring-border transition hover:ring-ring active:scale-[0.98]"
                        >
                            {t('common.back')}
                        </button>
                    {:else}
                        <span></span>
                    {/if}

                    {#if isLastStep}
                        <button
                            type="button"
                            onclick={generate}
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-6 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-110 active:scale-[0.98]"
                        >
                            <Sparkles class="size-5" />
                            {t('trips.generate')}
                        </button>
                    {:else}
                        <button
                            type="button"
                            onclick={() => (step = step + 1)}
                            class="inline-flex min-h-11 items-center justify-center gap-1 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-6 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-110 active:scale-[0.98]"
                        >
                            {t('common.next')}
                            <ChevronLeft class="size-4 ltr:rotate-180" />
                        </button>
                    {/if}
                </div>
            </div>
        {:else}
            {#if restoredNotice}
                <div
                    class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-2.5"
                >
                    <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">
                        {t('trips.restored')}
                    </p>
                    <button
                        type="button"
                        onclick={() => (restoredNotice = false)}
                        class="inline-flex size-8 items-center justify-center rounded-full text-emerald-700 dark:text-emerald-300 transition hover:bg-emerald-400/20"
                        aria-label={t('common.close')}
                    >
                        <X class="size-4" />
                    </button>
                </div>
            {/if}

            <div
                class="flex flex-wrap items-center gap-5 rounded-[20px] bg-[#0b1e33] p-6 text-white shadow-lg shadow-slate-900/10"
            >
                <div>
                    <p class="text-xs font-bold text-slate-300">
                        {t('trips.estimated')}
                    </p>
                    <p class="text-3xl font-bold">
                        {formatNumber(estimated)}
                        <span class="text-base font-bold">
                            {t('common.currency')}
                        </span>
                    </p>
                </div>
                <div class="h-12 w-px bg-white/15"></div>
                <div>
                    <p class="text-xs font-bold text-slate-300">
                        {t('trips.budget')}
                    </p>
                    <p class="text-3xl font-bold">
                        {formatNumber(budget)}
                        <span class="text-base font-bold">
                            {t('common.currency')}
                        </span>
                    </p>
                </div>
                <span
                    class="ms-auto rounded-full px-4 py-2 text-sm font-bold {estimated <=
                    budget
                        ? 'bg-emerald-400/15 text-emerald-300 ring-1 ring-emerald-400/30'
                        : 'bg-red-400/15 text-red-300 ring-1 ring-red-400/30'}"
                >
                    {estimated <= budget
                        ? t('trips.withinBudget')
                        : t('trips.overBudget')}
                </span>

                {#if totalPlaces > 0}
                    <div class="w-full border-t border-white/15 pt-4">
                        <div
                            class="flex items-center justify-between text-xs font-bold text-slate-300"
                        >
                            <span>
                                {t('trips.progress', {
                                    done: formatNumber(donePlaces),
                                    total: formatNumber(totalPlaces),
                                })}
                            </span>
                            <span>{formatNumber(progressPercent)}%</span>
                        </div>
                        <div
                            class="mt-2 h-2 overflow-hidden rounded-full bg-white/15"
                        >
                            <div
                                class="h-full rounded-full bg-gradient-to-l from-emerald-400 to-teal-700 transition-all"
                                style="width: {progressPercent}%"
                            ></div>
                        </div>
                    </div>
                {/if}
            </div>

            {#if shareState}
                <div
                    class="mt-5 rounded-[20px] bg-card p-5 shadow-sm ring-1 ring-border"
                >
                    <div class="flex items-start gap-3">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300"
                        >
                            <Share2 class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-foreground">
                                {t('trips.share')}
                            </h2>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {t('trips.shareHint')}
                            </p>
                        </div>
                    </div>

                    {#if shareState.isPublic && shareState.url}
                        <div class="mt-4 flex flex-col gap-3">
                            <span
                                class="inline-flex w-fit items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 ring-1 ring-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:ring-emerald-800"
                            >
                                <Check class="size-3.5" />
                                {t('trips.shared')}
                            </span>
                            <div class="flex min-w-0 items-center gap-2">
                                <p
                                    dir="ltr"
                                    class="min-w-0 flex-1 truncate rounded-xl bg-muted/60 px-3 py-2.5 text-sm text-muted-foreground ring-1 ring-border"
                                >
                                    {shareState.url}
                                </p>
                                <button
                                    type="button"
                                    onclick={copyShareLink}
                                    class="inline-flex min-h-10 shrink-0 items-center gap-2 rounded-xl bg-card px-3 text-sm font-bold text-secondary-foreground ring-1 ring-border transition hover:ring-emerald-300 active:scale-[0.98]"
                                >
                                    <Copy class="size-4" />
                                    {t('trips.copyLink')}
                                </button>
                            </div>
                            <button
                                type="button"
                                onclick={stopSharing}
                                disabled={shareHttp.processing}
                                class="inline-flex w-fit items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-50 disabled:opacity-60 dark:text-red-400 dark:hover:bg-red-950/40"
                            >
                                <Link2Off class="size-3.5" />
                                {t('trips.stopSharing')}
                            </button>
                        </div>
                    {:else}
                        <button
                            type="button"
                            onclick={sharePlan}
                            disabled={shareHttp.processing}
                            class="mt-4 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-6 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-110 active:scale-[0.98] disabled:opacity-60"
                        >
                            <Share2 class="size-4" />
                            {shareHttp.processing
                                ? t('trips.sharing')
                                : t('trips.share')}
                        </button>
                    {/if}
                </div>
            {/if}

            {#if draftPlaces.length > 0}
                <div
                    class="mt-5 rounded-[20px] bg-card p-5 shadow-sm ring-1 ring-border"
                >
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-bold text-foreground">
                            {t('trips.addedPlaces')}
                            <span class="text-muted-foreground">
                                ({formatNumber(draftPlaces.length)})
                            </span>
                        </h2>
                        <button
                            type="button"
                            onclick={clearTripDraft}
                            class="inline-flex min-h-10 items-center rounded-full px-3 text-sm font-bold text-red-600 dark:text-red-400 transition hover:bg-red-50 dark:hover:bg-red-950/40"
                        >
                            {t('common.clear')}
                        </button>
                    </div>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        {#each draftPlaces as place (place.id)}
                            <li>
                                <button
                                    type="button"
                                    onclick={() => toggleTripDraft(place.id)}
                                    class="inline-flex min-h-10 items-center gap-2 rounded-full bg-muted/60 px-3 text-sm font-bold text-secondary-foreground ring-1 ring-border transition hover:ring-ring"
                                >
                                    <span>{placeName(place)}</span>
                                    <X class="size-4 text-muted-foreground" />
                                </button>
                            </li>
                        {/each}
                    </ul>
                </div>
            {/if}

            <div class="mt-6 flex flex-col gap-4">
                <h2 class="text-xl font-bold text-foreground sm:text-2xl">
                    {t('trips.yourPlan')}
                    {#if selectedCities.length > 0}
                        —
                        {formatList(selectedCities.map((city) => cityName(city)))}
                    {/if}
                </h2>

                {#each plan as day, dayIndex (day.day)}
                    {@const dayCity =
                        day.cityId === null ? undefined : cityById(day.cityId)}
                    <div
                        class="rounded-[20px] bg-card p-5 shadow-sm ring-1 ring-border"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div
                                class="flex min-w-0 flex-wrap items-center gap-2"
                            >
                                <h3 class="text-base font-bold text-foreground">
                                    {t('trips.day', {
                                        day: formatNumber(day.day),
                                    })}
                                </h3>
                                {#if dayCity}
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-900"
                                    >
                                        <MapPin class="size-3.5" />
                                        {cityName(dayCity)}
                                    </span>
                                {/if}
                            </div>
                            <span class="text-xs font-bold text-muted-foreground">
                                {formatDate(day.date, {
                                    weekday: 'long',
                                    day: 'numeric',
                                    month: 'long',
                                })}
                            </span>
                        </div>

                        {#if day.entries.length === 0}
                            <p class="mt-3 text-sm text-muted-foreground">
                                {t('trips.restDay')}
                            </p>
                        {:else}
                            <ol class="mt-4 flex flex-col gap-3">
                                {#each day.entries as entry, entryIndex (entry.place.id)}
                                    {@const meta =
                                        categoryMeta[entry.place.category]}
                                    {@const placeCity = cityById(
                                        entry.place.cityId,
                                    )}
                                    <li class="flex items-stretch gap-3">
                                        <input
                                            type="time"
                                            value={toIsoTime(entry.minutes)}
                                            onchange={(event) =>
                                                updateEntryTime(
                                                    dayIndex,
                                                    entryIndex,
                                                    event.currentTarget.value,
                                                )}
                                            aria-label={t('trips.startTime')}
                                            class="w-24 shrink-0 rounded-xl px-2 py-3 text-center text-sm font-bold text-white outline-none {entry.done
                                                ? 'bg-emerald-600'
                                                : 'bg-[#0b1e33]'}"
                                        />
                                        <div
                                            class="min-w-0 flex-1 rounded-xl p-3 ring-1 {entry.done
                                                ? 'bg-emerald-50 dark:bg-emerald-950/40 ring-emerald-200 dark:ring-emerald-900/60'
                                                : 'bg-muted/60 ring-border'}"
                                        >
                                            <div
                                                class="flex flex-wrap items-center justify-between gap-2"
                                            >
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <span aria-hidden="true">
                                                        {meta.icon}
                                                    </span>
                                                    <p
                                                        class="font-bold {entry.done
                                                            ? 'text-muted-foreground line-through'
                                                            : 'text-foreground'}"
                                                    >
                                                        {placeName(entry.place)}
                                                    </p>
                                                </div>
                                                <button
                                                    type="button"
                                                    onclick={() =>
                                                        toggleDone(entry)}
                                                    aria-pressed={entry.done}
                                                    class="inline-flex min-h-8 shrink-0 items-center gap-1.5 rounded-full px-3 text-xs font-bold ring-1 transition active:scale-[0.98] {entry.done
                                                        ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 ring-emerald-300 dark:ring-emerald-800'
                                                        : 'bg-card text-muted-foreground ring-border hover:ring-emerald-300'}"
                                                >
                                                    {#if entry.done}
                                                        <Check
                                                            class="size-3.5"
                                                        />
                                                        {t('trips.visited')}
                                                    {:else}
                                                        <Circle
                                                            class="size-3.5"
                                                        />
                                                        {t(
                                                            'trips.markVisited',
                                                        )}
                                                    {/if}
                                                </button>
                                            </div>
                                            <p
                                                class="mt-1 flex flex-wrap items-center gap-3 text-xs text-muted-foreground"
                                            >
                                                {#if placeCity}
                                                    <span
                                                        class="flex items-center gap-1"
                                                    >
                                                        <MapPin
                                                            class="size-3.5"
                                                        />
                                                        {cityName(placeCity)}
                                                    </span>
                                                {/if}
                                                <span>
                                                    {t(
                                                        `categories.${entry
                                                            .place.category}`,
                                                    )}
                                                </span>
                                                <span
                                                    class="flex items-center gap-1"
                                                >
                                                    <Star
                                                        class="size-3.5 fill-amber-400 text-amber-400"
                                                    />
                                                    {formatNumber(
                                                        entry.place.rating,
                                                    )}
                                                </span>
                                                <span
                                                    class="font-bold text-foreground"
                                                >
                                                    {priceLabel(entry.place)}
                                                </span>
                                            </p>
                                            <div
                                                class="mt-2 flex items-center justify-end gap-1"
                                            >
                                                <button
                                                    type="button"
                                                    onclick={() =>
                                                        moveEntry(
                                                            dayIndex,
                                                            entryIndex,
                                                            'up',
                                                        )}
                                                    disabled={entryIndex === 0}
                                                    class="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition hover:bg-background hover:text-foreground disabled:opacity-30 disabled:hover:bg-transparent"
                                                    aria-label={t(
                                                        'trips.moveUp',
                                                    )}
                                                    title={t('trips.moveUp')}
                                                >
                                                    <ChevronUp class="size-4" />
                                                </button>
                                                <button
                                                    type="button"
                                                    onclick={() =>
                                                        moveEntry(
                                                            dayIndex,
                                                            entryIndex,
                                                            'down',
                                                        )}
                                                    disabled={entryIndex ===
                                                        day.entries.length - 1}
                                                    class="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition hover:bg-background hover:text-foreground disabled:opacity-30 disabled:hover:bg-transparent"
                                                    aria-label={t(
                                                        'trips.moveDown',
                                                    )}
                                                    title={t('trips.moveDown')}
                                                >
                                                    <ChevronDown
                                                        class="size-4"
                                                    />
                                                </button>
                                                <button
                                                    type="button"
                                                    onclick={() =>
                                                        removeEntry(
                                                            dayIndex,
                                                            entryIndex,
                                                        )}
                                                    class="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition hover:bg-background hover:text-red-600"
                                                    aria-label={t(
                                                        'trips.removeItem',
                                                    )}
                                                    title={t('trips.removeItem')}
                                                >
                                                    <Trash2 class="size-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                {/each}
                            </ol>
                        {/if}

                        {#if dayCity && availablePlaces(day).length > 0}
                            <select
                                value=""
                                onchange={(event) => {
                                    const value = Number(
                                        event.currentTarget.value,
                                    );

                                    if (value > 0) {
                                        addEntry(dayIndex, value);
                                    }

                                    event.currentTarget.value = '';
                                }}
                                class="mt-3 min-h-11 w-full rounded-xl bg-muted/60 px-3 text-sm font-bold text-secondary-foreground outline-none ring-1 ring-border transition focus:ring-2 focus:ring-emerald-400"
                            >
                                <option value="">{t('trips.addPlace')}</option>
                                {#each availablePlaces(day) as place (place.id)}
                                    <option value={place.id}>
                                        {placeName(place)}
                                    </option>
                                {/each}
                            </select>
                        {/if}
                    </div>
                {/each}
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    onclick={restart}
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-card px-5 text-sm font-bold text-secondary-foreground ring-1 ring-border transition hover:ring-ring active:scale-[0.98]"
                >
                    <RotateCcw class="size-5" />
                    {t('trips.regenerate')}
                </button>
                {#if shareState}
                    <Link
                        href={printTrip.url(shareState.id)}
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-card px-5 text-sm font-bold text-secondary-foreground ring-1 ring-border transition hover:ring-ring active:scale-[0.98]"
                    >
                        <Printer class="size-5" />
                        {t('trips.export')}
                    </Link>
                {/if}
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

<Toaster />
