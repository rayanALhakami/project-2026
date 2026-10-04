<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import CalendarDays from '@lucide/svelte/icons/calendar-days';
    import Check from '@lucide/svelte/icons/check';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import Clock from '@lucide/svelte/icons/clock';
    import Compass from '@lucide/svelte/icons/compass';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Share2 from '@lucide/svelte/icons/share-2';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Users from '@lucide/svelte/icons/users';
    import Wallet from '@lucide/svelte/icons/wallet';
    import AppHead from '@/components/AppHead.svelte';
    import SiteHeader from '@/components/SiteHeader.svelte';
    import { Toaster } from '@/components/ui/sonner';
    import { formatDate, formatNumber, getLocale, t } from '@/lib/i18n.svelte';
    import { cityName, placeName } from '@/lib/localize';
    import { categoryMeta, cityById, placeById } from '@/lib/tourism.svelte';
    import { register, trips } from '@/routes';

    interface SharedTripItem {
        id: number;
        place_id: number | null;
        title: string;
        type: string;
        start_time: string | null;
        duration_minutes: number | null;
        notes: string | null;
        completed: boolean;
    }

    interface SharedTripDay {
        day_number: number;
        date: string | null;
        items: SharedTripItem[];
    }

    interface SharedTrip {
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
        days: SharedTripDay[];
    }

    let { trip }: { trip: SharedTrip } = $props();

    const authUser = $derived(page.props.auth.user);
    const ctaHref = $derived(authUser ? trips().url : register().url);

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

    const progressPercent = $derived(
        trip.items_count > 0
            ? Math.round((trip.completed_count / trip.items_count) * 100)
            : 0,
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
</script>

<AppHead title={trip.title || t('shared.title')} />

<SiteHeader active="" />

<div class="min-h-dvh bg-background">
    <section
        class="bg-gradient-to-br from-[#16344f] via-[#0b2337] to-[#071523] pb-16 pt-28 text-white"
    >
        <div class="mx-auto w-full max-w-3xl px-4 md:px-6">
            <span
                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-emerald-200 ring-1 ring-white/15"
            >
                <Share2 class="size-4" aria-hidden="true" />
                {t('shared.badge')}
            </span>
            <h1 class="mt-4 text-3xl font-bold sm:text-4xl">{trip.title}</h1>
            <p class="mt-2 max-w-2xl text-slate-300">{t('shared.subtitle')}</p>

            <div
                class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-bold text-slate-200"
            >
                {#if cityLabel}
                    <span class="flex items-center gap-1.5">
                        <MapPin class="size-4" aria-hidden="true" />
                        {cityLabel}
                    </span>
                {/if}
                <span class="flex items-center gap-1.5">
                    <CalendarDays class="size-4" aria-hidden="true" />
                    {formatDate(trip.start_date)}
                    <span aria-hidden="true">–</span>
                    {formatDate(trip.end_date)}
                </span>
                <span class="flex items-center gap-1.5">
                    <Compass class="size-4" aria-hidden="true" />
                    {formatNumber(trip.days_count)}
                    {t('common.days')}
                </span>
                <span class="flex items-center gap-1.5">
                    <Users class="size-4" aria-hidden="true" />
                    {formatNumber(trip.travelers_count)}
                    {t('common.persons')}
                </span>
                {#if budgetLabel}
                    <span class="flex items-center gap-1.5">
                        <Wallet class="size-4" aria-hidden="true" />
                        {budgetLabel}
                    </span>
                {/if}
            </div>
        </div>
    </section>

    <div class="mx-auto -mt-8 w-full max-w-3xl px-4 pb-20 md:px-6">
        <div
            class="rounded-[20px] bg-card p-5 shadow-lg shadow-slate-900/5 ring-1 ring-border"
        >
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p
                    class="flex items-center gap-2 text-sm font-bold text-secondary-foreground"
                >
                    <CheckCircle2
                        class="size-4 text-emerald-600 dark:text-emerald-400"
                        aria-hidden="true"
                    />
                    {t('shared.readOnly')}
                </p>
                {#if trip.items_count > 0}
                    <p class="text-sm font-bold text-muted-foreground">
                        {t('trips.progress', {
                            done: formatNumber(trip.completed_count),
                            total: formatNumber(trip.items_count),
                        })}
                    </p>
                {/if}
            </div>
            {#if trip.items_count > 0}
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full rounded-full bg-gradient-to-l from-emerald-400 to-teal-700 transition-all"
                        style="width: {progressPercent}%"
                    ></div>
                </div>
            {/if}
        </div>

        <div class="mt-5 flex flex-col gap-4">
            {#each trip.days as day (day.day_number)}
                <div
                    class="rounded-[20px] bg-card p-5 shadow-sm ring-1 ring-border"
                >
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-bold text-foreground">
                            {t('trips.day', {
                                day: formatNumber(day.day_number),
                            })}
                        </h2>
                        {#if day.date}
                            <span
                                class="text-xs font-bold text-muted-foreground"
                            >
                                {formatDate(day.date, {
                                    weekday: 'long',
                                    day: 'numeric',
                                    month: 'long',
                                })}
                            </span>
                        {/if}
                    </div>

                    {#if day.items.length === 0}
                        <p class="mt-3 text-sm text-muted-foreground">
                            {t('trips.restDay')}
                        </p>
                    {:else}
                        <ol class="mt-4 flex flex-col gap-3">
                            {#each day.items as item (item.id)}
                                {@const place = item.place_id
                                    ? placeById(item.place_id)
                                    : undefined}
                                {@const meta = place
                                    ? categoryMeta[place.category]
                                    : null}
                                {@const duration =
                                    item.duration_minutes ??
                                    place?.avgVisitDuration ??
                                    0}
                                <li class="flex items-stretch gap-3">
                                    {#if item.start_time}
                                        <span
                                            class="flex w-24 shrink-0 items-center justify-center rounded-xl bg-[#0b1e33] px-2 py-3 text-center text-sm font-bold text-white"
                                        >
                                            {formatTime(item.start_time)}
                                        </span>
                                    {/if}
                                    <div
                                        class="min-w-0 flex-1 rounded-xl p-3 ring-1 {item.completed
                                            ? 'bg-emerald-50 ring-emerald-200 dark:bg-emerald-950/40 dark:ring-emerald-900/60'
                                            : 'bg-muted/60 ring-border'}"
                                    >
                                        <div
                                            class="flex flex-wrap items-center justify-between gap-2"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                {#if meta}
                                                    <span aria-hidden="true"
                                                        >{meta.icon}</span
                                                    >
                                                {:else}
                                                    <span
                                                        class="size-2.5 rounded-full bg-muted-foreground/40"
                                                        aria-hidden="true"
                                                    ></span>
                                                {/if}
                                                <p
                                                    class="font-bold {item.completed
                                                        ? 'text-muted-foreground line-through'
                                                        : 'text-foreground'}"
                                                >
                                                    {place
                                                        ? placeName(place)
                                                        : item.title}
                                                </p>
                                            </div>
                                            {#if item.completed}
                                                <span
                                                    class="inline-flex min-h-8 shrink-0 items-center gap-1.5 rounded-full bg-emerald-100 px-3 text-xs font-bold text-emerald-800 ring-1 ring-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:ring-emerald-800"
                                                >
                                                    <Check
                                                        class="size-3.5"
                                                        aria-hidden="true"
                                                    />
                                                    {t('trips.visited')}
                                                </span>
                                            {/if}
                                        </div>
                                        {#if place || duration > 0}
                                            <p
                                                class="mt-1 flex flex-wrap items-center gap-3 text-xs text-muted-foreground"
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
                                                            class="size-3.5"
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
                                            </p>
                                        {/if}
                                        {#if item.notes}
                                            <p
                                                class="mt-2 text-xs text-muted-foreground"
                                            >
                                                {item.notes}
                                            </p>
                                        {/if}
                                    </div>
                                </li>
                            {/each}
                        </ol>
                    {/if}
                </div>
            {/each}
        </div>

        <div
            class="mt-6 rounded-[20px] bg-card p-6 shadow-sm ring-1 ring-border"
        >
            <div class="flex items-start gap-3">
                <span
                    class="flex size-11 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300"
                >
                    <Sparkles class="size-5" aria-hidden="true" />
                </span>
                <div class="min-w-0">
                    <h2 class="text-base font-bold text-foreground">
                        {t('shared.cta')}
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {t('shared.ctaHint')}
                    </p>
                </div>
            </div>
            <Link
                href={ctaHref}
                class="mt-5 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-6 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-110 active:scale-[0.98] sm:w-auto"
            >
                <Compass class="size-4" aria-hidden="true" />
                {t('shared.cta')}
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
