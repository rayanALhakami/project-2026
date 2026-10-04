<script lang="ts">
    import type { Snippet } from 'svelte';
    import AppContent from '@/components/AppContent.svelte';
    import AppFooter from '@/components/AppFooter.svelte';
    import AppShell from '@/components/AppShell.svelte';
    import AppSidebar from '@/components/AppSidebar.svelte';
    import AppSidebarHeader from '@/components/AppSidebarHeader.svelte';
    import BottomNav from '@/components/BottomNav.svelte';
    import Onboarding from '@/components/Onboarding.svelte';
    import PlaceDetailsSheet from '@/components/PlaceDetailsSheet.svelte';
    import { Toaster } from '@/components/ui/sonner';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();
</script>

<AppShell variant="sidebar">
    <AppSidebar />
    <AppContent
        variant="sidebar"
        class="min-w-0 overflow-x-clip pb-bottom-nav md:pb-0"
    >
        <AppSidebarHeader {breadcrumbs} />
        {@render children?.()}
        <AppFooter />
    </AppContent>
    <BottomNav />
    <PlaceDetailsSheet />
    <Onboarding />
    <Toaster />
</AppShell>
