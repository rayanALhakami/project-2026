<script lang="ts">
    import Moon from '@lucide/svelte/icons/moon';
    import Sun from '@lucide/svelte/icons/sun';
    import { t } from '@/lib/i18n.svelte';
    import { themeState } from '@/lib/theme.svelte';
    import { cn } from '@/lib/utils';

    let { class: className = '' }: { class?: string } = $props();

    const { resolvedAppearance, updateAppearance } = themeState();

    const isDark = $derived(resolvedAppearance() === 'dark');
    const label = $derived(
        isDark ? t('common.lightMode') : t('common.darkMode'),
    );

    function toggle(): void {
        updateAppearance(isDark ? 'light' : 'dark');
    }
</script>

<button
    type="button"
    onclick={toggle}
    class={cn(
        'inline-flex size-11 shrink-0 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-muted hover:text-foreground',
        className,
    )}
    aria-label={label}
    title={label}
>
    {#if isDark}
        <Sun class="size-5" aria-hidden="true" />
    {:else}
        <Moon class="size-5" aria-hidden="true" />
    {/if}
</button>
