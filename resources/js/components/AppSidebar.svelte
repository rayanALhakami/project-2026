<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ChartPie from '@lucide/svelte/icons/chart-pie';
    import LayoutGrid from '@lucide/svelte/icons/layout-grid';
    import Receipt from '@lucide/svelte/icons/receipt';
    import Settings from '@lucide/svelte/icons/settings';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Tags from '@lucide/svelte/icons/tags';
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
    import { assistant, dashboard, reports } from '@/routes';
    import categories from '@/routes/categories';
    import { edit } from '@/routes/profile';
    import transactions from '@/routes/transactions';
    import type { NavItem } from '@/types';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const mainNavItems: NavItem[] = [
        {
            title: 'الرئيسية',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'المساعد',
            href: assistant(),
            icon: Sparkles,
        },
        {
            title: 'المعاملات',
            href: transactions.index(),
            icon: Receipt,
        },
        {
            title: 'التقارير',
            href: reports(),
            icon: ChartPie,
        },
        {
            title: 'الفئات',
            href: categories.index(),
            icon: Tags,
        },
    ];

    const footerNavItems: NavItem[] = [
        {
            title: 'الإعدادات',
            href: edit(),
            icon: Settings,
        },
    ];
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg">
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
