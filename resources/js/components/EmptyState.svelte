<script lang="ts">
    import type { Snippet } from 'svelte';

    let {
        icon,
        title,
        description = '',
        action,
    }: {
        icon?: string;
        title: string;
        description?: string;
        action?: Snippet;
    } = $props();
</script>

<div
    class="flex flex-col items-center justify-center gap-3 rounded-[18px] border border-dashed border-border/70 px-6 py-16 text-center"
>
    {#if icon}
        <span class="text-4xl">{icon}</span>
    {/if}
    <p class="text-base font-semibold text-foreground">{title}</p>
    {#if description}
        <p class="max-w-xs text-sm text-muted-foreground">{description}</p>
    {/if}
    {#if action}
        <div class="mt-2">{@render action()}</div>
    {/if}
</div>
