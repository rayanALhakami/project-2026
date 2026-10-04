<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ChartColumn from '@lucide/svelte/icons/chart-column';
    import Heart from '@lucide/svelte/icons/heart';
    import Languages from '@lucide/svelte/icons/languages';
    import LayoutGrid from '@lucide/svelte/icons/layout-grid';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Route from '@lucide/svelte/icons/route';
    import Settings from '@lucide/svelte/icons/settings';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavFooter from '@/components/NavFooter.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { toUrl } from '@/lib/utils';
    import { t } from '@/lib/i18n.svelte';
    import {
        analytics,
        assistant,
        dashboard,
        favorites,
        places,
        translate,
        trips,
    } from '@/routes';
    import { edit } from '@/routes/profile';
    import type { NavItem } from '@/types';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const mainNavItems = $derived<NavItem[]>([
        {
            title: t('nav.home'),
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: t('nav.places'),
            href: places(),
            icon: MapPin,
        },
        {
            title: t('nav.trips'),
            href: trips(),
            icon: Route,
        },
        {
            title: t('nav.favorites'),
            href: favorites(),
            icon: Heart,
        },
        {
            title: t('analytics.title'),
            href: analytics(),
            icon: ChartColumn,
        },
        {
            title: t('nav.assistant'),
            href: assistant(),
            icon: Sparkles,
        },
        {
            title: t('nav.translate'),
            href: translate(),
            icon: Languages,
        },
    ]);

    const footerNavItems = $derived<NavItem[]>([
        {
            title: t('nav.settings'),
            href: edit(),
            icon: Settings,
        },
    ]);
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg" class="h-auto min-h-12 py-2">
                    {#snippet child({ props })}
                        <Link {...props} href={toUrl(dashboard())}>
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain items={mainNavItems} />
    </SidebarContent>

    <SidebarFooter>
        <NavFooter items={footerNavItems} />
        <NavUser />
    </SidebarFooter>
</Sidebar>
{@render children?.()}
