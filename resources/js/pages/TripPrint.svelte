<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import CalendarDays from '@lucide/svelte/icons/calendar-days';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import Clock from '@lucide/svelte/icons/clock';
    import Compass from '@lucide/svelte/icons/compass';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Printer from '@lucide/svelte/icons/printer';
    import Users from '@lucide/svelte/icons/users';
    import Wallet from '@lucide/svelte/icons/wallet';
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import {
        formatDate,
        formatFullDate,
        formatNumber,
        getLocale,
        t,
    } from '@/lib/i18n.svelte';
    import { cityName, placeName } from '@/lib/localize';
    import { cityById, placeById } from '@/lib/tourism.svelte';
    import { trips } from '@/routes';

    interface TripPrintItem {
        id: number;
        place_id: number | null;
        title: string;
        type: string;
        start_time: string | null;
        duration_minutes: number | null;
        notes: string | null;
        completed: boolean;
    }

    interface TripPrintDay {
        day_number: number;
        date: string | null;
        items: TripPrintItem[];
    }

    interface Trip {
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
        share_token: string | null;
        is_public: boolean;
        share_url: string | null;
        days: TripPrintDay[];
    }

    let { trip }: { trip: Trip } = $props();

    const city = $derived(trip.city_id ? cityById(trip.city_id) : undefined);
    const cityLabel = $derived.by(() => {
        if (city) {
            return cityName(city);
        }

        return (getLocale() === 'ar' ? trip.city_name : trip.city_name_en) ?? '';
    });

    const budgetLabel = $derived(
        trip.budget !== null
            ? `${formatNumber(Number(trip.budget))} ${t('common.currency')}`
            : null,
    );

    function formatTime(value: string): string {
        const [hours, minutes] = value.split(':').map(Number);
        const date = new Date();
        date.setHours(hours || 0, minutes || 0, 0, 0);

        return new Intl.DateTimeFormat(getLocale(), {
            hour: 'numeric',
            minute: '2-digit',
        }).format(date);
    }

    onMount(() => {
        const timer = window.setTimeout(() => window.print(), 700);

        return () => window.clearTimeout(timer);
    });
</script>

<AppHead title={trip.title} />

<div class="min-h-dvh bg-background print:bg-white">
    <div class="border-b border-border bg-card print:hidden">
        <div
            class="mx-auto flex w-full max-w-3xl flex-wrap items-center gap-3 px-4 py-3 md:px-6"
        >
            <Link
                href={trips().url}
                class="inline-flex min-h-10 shrink-0 items-center gap-1.5 rounded-xl bg-card px-3 text-sm font-bold text-secondary-foreground ring-1 ring-border transition hover:ring-ring active:scale-[0.98]"
            >
                <ChevronLeft class="size-4 ltr:rotate-180" aria-hidden="true" />
                {t('common.back')}
            </Link>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold text-foreground">
                    {t('trips.exportTitle')}
                </p>
                <p class="truncate text-xs text-muted-foreground">
                    {t('trips.exportHint')}
                </p>
            </div>
            <button
                type="button"
                onclick={() => window.print()}
                class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-4 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-110 active:scale-[0.98]"
            >
                <Printer class="size-4" aria-hidden="true" />
                {t('trips.printNow')}
            </button>
        </div>
    </div>

    <main
        class="mx-auto w-full max-w-3xl bg-white px-4 py-6 text-slate-900 md:px-6 print:max-w-none print:px-0 print:py-0"
    >
        <header class="border-b border-slate-200 pb-4">
            <h1 class="text-2xl font-bold text-slate-900">{trip.title}</h1>
            <p class="mt-1 text-xs text-slate-500">
                {t('trips.exportedOn', { date: formatFullDate() })}
            </p>
            <div
                class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs font-bold text-slate-600"
            >
                {#if cityLabel}
                    <span class="flex items-center gap-1.5">
                        <MapPin
                            class="size-3.5 text-slate-400"
                            aria-hidden="true"
                        />
                        {cityLabel}
                    </span>
                {/if}
                <span class="flex items-center gap-1.5">
                    <CalendarDays
                        class="size-3.5 text-slate-400"
                        aria-hidden="true"
                    />
                    {formatDate(trip.start_date)}
                    <span aria-hidden="true">–</span>
                    {formatDate(trip.end_date)}
                </span>
                <span class="flex items-center gap-1.5">
                    <Compass class="size-3.5 text-slate-400" aria-hidden="true" />
                    {formatNumber(trip.days_count)}
                    {t('common.days')}
                </span>
                <span class="flex items-center gap-1.5">
                    <Users class="size-3.5 text-slate-400" aria-hidden="true" />
                    {formatNumber(trip.travelers_count)}
                    {t('common.persons')}
                </span>
                {#if budgetLabel}
                    <span class="flex items-center gap-1.5">
                        <Wallet
                            class="size-3.5 text-slate-400"
                            aria-hidden="true"
                        />
                        {budgetLabel}
                    </span>
                {/if}
            </div>
        </header>

        <div class="mt-4 flex flex-col gap-3">
            {#each trip.days as day (day.day_number)}
                <section
                    class="break-inside-avoid rounded-xl border border-slate-200 p-4"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <h2 class="text-base font-bold text-slate-900">
                            {t('trips.day', {
                                day: formatNumber(day.day_number),
                            })}
                        </h2>
                        {#if day.date}
                            <span class="text-xs font-bold text-slate-500">
                                {formatDate(day.date, {
                                    weekday: 'long',
                                    day: 'numeric',
                                    month: 'long',
                                })}
                            </span>
                        {/if}
                    </div>

                    {#if day.items.length === 0}
                        <p class="mt-2 text-sm text-slate-500">
                            {t('trips.restDay')}
                        </p>
                    {:else}
                        <ol class="mt-3 flex flex-col gap-2">
                            {#each day.items as item (item.id)}
                                {@const place = item.place_id
                                    ? placeById(item.place_id)
                                    : undefined}
                                {@const duration =
                                    item.duration_minutes ??
                                    place?.avgVisitDuration ??
                                    0}
                                <li class="flex items-start gap-3 text-sm">
                                    {#if item.start_time}
                                        <span
                                            class="w-16 shrink-0 text-xs font-bold text-slate-500"
                                        >
                                            {formatTime(item.start_time)}
                                        </span>
                                    {/if}
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="font-bold {item.completed
                                                ? 'text-slate-400 line-through'
                                                : 'text-slate-900'}"
                                        >
                                            {place
                                                ? placeName(place)
                                                : item.title}
                                        </p>
                                        {#if place || duration > 0 || item.completed}
                                            <p
                                                class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-slate-500"
                                            >
                                                {#if place}
                                                    <span>
                                                        {t(
                                                            `categories.${place.category}`,
                                                        )}
                                                    </span>
                                                {/if}
                                                {#if duration > 0}
                                                    <span
                                                        class="flex items-center gap-1"
                                                    >
                                                        <Clock
                                                            class="size-3.5 text-slate-400"
                                                            aria-hidden="true"
                                                        />
                                                        {t(
                                                            'place.durationMinutes',
                                                            {
                                                                count: formatNumber(
                                                                    duration,
                                                                ),
                                                            },
                                                        )}
                                                    </span>
                                                {/if}
                                                {#if item.completed}
                                                    <span
                                                        class="flex items-center gap-1 font-bold text-emerald-700"
                                                    >
                                                        <CheckCircle2
                                                            class="size-3.5"
                                                            aria-hidden="true"
                                                        />
                                                        {t('trips.visited')}
                                                    </span>
                                                {/if}
                                            </p>
                                        {/if}
                                        {#if item.notes}
                                            <p
                                                class="mt-1 text-xs text-slate-500"
                                            >
                                                {item.notes}
                                            </p>
                                        {/if}
                                    </div>
                                </li>
                            {/each}
                        </ol>
                    {/if}
                </section>
            {/each}
        </div>

        <footer
            class="mt-6 flex flex-col gap-1 border-t border-slate-200 pt-3 text-xs text-slate-500"
        >
            <p>
                © {new Date().getFullYear()}
                {t('app.name')}
                <span aria-hidden="true">—</span>
                {t('preview.rights')}
            </p>
            <p class="print:hidden">{t('trips.exportHint')}</p>
        </footer>
    </main>
</div>

<style>
    @media print {
        @page {
            margin: 12mm;
        }

        :global(body) {
            background: white !important;
        }
    }
</style>
