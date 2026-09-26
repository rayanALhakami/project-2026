<script lang="ts">
    import Moon from '@lucide/svelte/icons/moon';
    import Sun from '@lucide/svelte/icons/sun';
    import { themeState } from '@/lib/theme.svelte';

    const { resolvedAppearance, updateAppearance } = themeState();

    const isDark = $derived(resolvedAppearance() === 'dark');

    function toggle(): void {
        updateAppearance(isDark ? 'light' : 'dark');
    }
</script>

<button
    type="button"
    onclick={toggle}
    class="inline-flex size-8 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
    aria-label="تبديل المظهر"
>
    {#if isDark}
        <Sun class="size-4" />
    {:else}
        <Moon class="size-4" />
    {/if}
</button>
