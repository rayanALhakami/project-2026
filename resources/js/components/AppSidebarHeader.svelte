<script lang="ts">
    import Breadcrumbs from '@/components/Breadcrumbs.svelte';
    import FontSizeControl from '@/components/FontSizeControl.svelte';
    import LanguageSwitcher from '@/components/LanguageSwitcher.svelte';
    import { SidebarTrigger } from '@/components/ui/sidebar';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs = [],
    }: {
        breadcrumbs?: BreadcrumbItem[];
    } = $props();
</script>

<header
    class="sticky top-0 z-30 flex min-h-14 shrink-0 items-center gap-2 border-b border-border/70 bg-background/80 px-4 backdrop-blur-xl backdrop-saturate-150"
>
    <div class="hidden md:block">
        <SidebarTrigger />
    </div>
    {#if breadcrumbs && breadcrumbs.length > 0}
        <Breadcrumbs {breadcrumbs} />
    {/if}
    <div class="ms-auto flex items-center gap-1">
        <FontSizeControl />
        <LanguageSwitcher />
    </div>
</header>
