<script lang="ts">
    import type { LinkComponentBaseProps } from '@inertiajs/core';
    import ChevronRight from '@lucide/svelte/icons/chevron-right';
    import ActionButton from '@/components/ActionButton.svelte';

    let {
        title,
        actionLabel = '',
        actionHref,
    }: {
        title: string;
        actionLabel?: string;
        actionHref?: LinkComponentBaseProps['href'];
    } = $props();
</script>

<div class="flex flex-wrap items-center justify-between gap-3">
    <h2 class="text-xl font-semibold tracking-tight text-foreground sm:text-2xl">
        {title}
    </h2>
    {#if actionHref}
        <ActionButton
            variant="ghost"
            href={actionHref}
            class="min-h-11 px-3 text-primary"
        >
            {actionLabel}
            <ChevronRight class="size-4 rtl:rotate-180" />
        </ActionButton>
    {/if}
</div>
