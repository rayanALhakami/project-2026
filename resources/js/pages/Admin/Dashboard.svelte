<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Building2 from '@lucide/svelte/icons/building-2';
    import CalendarDays from '@lucide/svelte/icons/calendar-days';
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Route from '@lucide/svelte/icons/route';
    import Star from '@lucide/svelte/icons/star';
    import Users from '@lucide/svelte/icons/users';
    import AdminNav from '@/components/AdminNav.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { formatNumber, t } from '@/lib/i18n.svelte';
    import { index as adminEvents } from '@/routes/admin/events';
    import { index as adminPlaces } from '@/routes/admin/places';
    import { index as adminReviews } from '@/routes/admin/reviews';
    import type { Component } from 'svelte';

    interface AdminStats {
        places: number;
        cities: number;
        events: number;
        reviews: number;
        users: number;
        trips: number;
    }

    let {
        stats,
    }: {
        stats: AdminStats;
    } = $props();

    type StatCard = {
        key: keyof AdminStats;
        label: string;
        icon: Component<{ class?: string }>;
    };

    const cards = $derived<StatCard[]>([
        { key: 'places', label: t('admin.stats.places'), icon: MapPin },
        { key: 'cities', label: t('admin.stats.cities'), icon: Building2 },
        { key: 'events', label: t('admin.stats.events'), icon: CalendarDays },
        { key: 'reviews', label: t('admin.stats.reviews'), icon: Star },
        { key: 'users', label: t('admin.stats.users'), icon: Users },
        { key: 'trips', label: t('admin.stats.trips'), icon: Route },
    ]);

    const sections = $derived([
        {
            key: 'places',
            title: t('admin.places'),
            href: adminPlaces.url(),
            icon: MapPin,
        },
        {
            key: 'events',
            title: t('admin.events'),
            href: adminEvents.url(),
            icon: CalendarDays,
        },
        {
            key: 'reviews',
            title: t('admin.reviews'),
            href: adminReviews.url(),
            icon: Star,
        },
    ]);
</script>

<AppHead title={t('admin.title')} />

<div class="mx-auto w-full max-w-6xl px-4 py-6 md:px-6">
    <AdminNav />

    <header class="mt-6">
        <h1 class="text-2xl font-bold text-foreground sm:text-3xl">
            {t('admin.title')}
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">{t('admin.subtitle')}</p>
    </header>

    <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-3">
        {#each cards as card (card.key)}
            <div
                class="flex items-center gap-3 rounded-[20px] bg-card p-5 shadow-sm ring-1 ring-border"
            >
                <span
                    class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-400 dark:ring-emerald-900/60"
                >
                    <card.icon class="size-5" />
                </span>
                <span class="min-w-0">
                    <span class="block text-xs font-bold text-muted-foreground">
                        {card.label}
                    </span>
                    <span class="block text-2xl font-bold text-foreground">
                        {formatNumber(stats[card.key])}
                    </span>
                </span>
            </div>
        {/each}
    </div>

    <section class="mt-8">
        <h2 class="text-xl font-bold text-foreground">
            {t('admin.dashboard')}
        </h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-3">
            {#each sections as section (section.key)}
                <Link
                    href={section.href}
                    class="flex items-center gap-3 rounded-[20px] bg-card p-5 shadow-sm ring-1 ring-border transition hover:-translate-y-0.5 hover:shadow-md active:scale-[0.98]"
                >
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-400 dark:ring-emerald-900/60"
                    >
                        <section.icon class="size-5" />
                    </span>
                    <span class="text-sm font-bold text-foreground">
                        {section.title}
                    </span>
                    <ChevronLeft
                        class="ms-auto size-4 shrink-0 text-muted-foreground ltr:rotate-180"
                    />
                </Link>
            {/each}
        </div>
    </section>
</div>
