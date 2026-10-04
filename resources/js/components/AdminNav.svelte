<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import CalendarDays from '@lucide/svelte/icons/calendar-days';
    import LayoutDashboard from '@lucide/svelte/icons/layout-dashboard';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Star from '@lucide/svelte/icons/star';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { t } from '@/lib/i18n.svelte';
    import { dashboard as adminDashboard } from '@/routes/admin';
    import { index as adminEvents } from '@/routes/admin/events';
    import { index as adminPlaces } from '@/routes/admin/places';
    import { index as adminReviews } from '@/routes/admin/reviews';
    import { dashboard as appDashboard } from '@/routes';
    import type { Component } from 'svelte';

    type AdminTab = {
        key: string;
        title: string;
        href: string;
        icon: Component<{ class?: string }>;
        exact: boolean;
    };

    const url = currentUrlState();

    const tabs = $derived<AdminTab[]>([
        {
            key: 'dashboard',
            title: t('admin.dashboard'),
            href: adminDashboard.url(),
            icon: LayoutDashboard,
            exact: true,
        },
        {
            key: 'places',
            title: t('admin.places'),
            href: adminPlaces.url(),
            icon: MapPin,
            exact: false,
        },
        {
            key: 'events',
            title: t('admin.events'),
            href: adminEvents.url(),
            icon: CalendarDays,
            exact: false,
        },
        {
            key: 'reviews',
            title: t('admin.reviews'),
            href: adminReviews.url(),
            icon: Star,
            exact: false,
        },
    ]);
</script>

<nav
    class="flex flex-wrap items-center gap-2 rounded-[18px] bg-card p-2 shadow-sm ring-1 ring-border"
    aria-label={t('admin.title')}
>
    {#each tabs as tab (tab.key)}
        {@const active = tab.exact
            ? url.isCurrentUrl(tab.href, url.currentUrl)
            : url.isCurrentOrParentUrl(tab.href, url.currentUrl)}
        <Link
            href={tab.href}
            aria-current={active ? 'page' : undefined}
            class="inline-flex min-h-10 items-center gap-2 rounded-xl px-4 text-sm font-bold transition {active
                ? 'bg-[#0b1e33] text-white'
                : 'text-muted-foreground hover:bg-muted hover:text-foreground'}"
        >
            <tab.icon class="size-4" />
            {tab.title}
        </Link>
    {/each}

    <Link
        href={appDashboard.url()}
        class="ms-auto inline-flex min-h-10 items-center gap-2 rounded-xl px-4 text-sm font-bold text-muted-foreground transition hover:bg-muted hover:text-foreground"
    >
        <ArrowLeft class="size-4 rtl:rotate-180" />
        {t('admin.backToApp')}
    </Link>
</nav>
