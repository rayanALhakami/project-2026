<script lang="ts">
    import Monitor from '@lucide/svelte/icons/monitor';
    import Moon from '@lucide/svelte/icons/moon';
    import Sun from '@lucide/svelte/icons/sun';
    import type { Component, SvelteComponent } from 'svelte';
    import { t } from '@/lib/i18n.svelte';
    import { themeState } from '@/lib/theme.svelte';
    import type { Appearance } from '@/types';

    const { appearance, updateAppearance } = themeState();

    type IconComponent =
        | Component<{ class?: string }>
        | (new (...args: any[]) => SvelteComponent<{ class?: string }>);

    const tabs = $derived<{ value: Appearance; Icon: IconComponent; label: string }[]>([
        { value: 'light', Icon: Sun, label: t('common.lightMode') },
        { value: 'dark', Icon: Moon, label: t('common.darkMode') },
        { value: 'system', Icon: Monitor, label: t('common.systemMode') },
    ]);

    function handleAppearanceChange(value: Appearance) {
        updateAppearance(value);
    }
</script>

<div class="inline-flex gap-1 rounded-xl bg-muted p-1">
    {#each tabs as { value, Icon, label } (value)}
        <button
            type="button"
            onclick={() => handleAppearanceChange(value)}
            class="flex items-center rounded-lg px-3.5 py-1.5 transition-colors {appearance.value ===
            value
                ? 'bg-card font-bold text-foreground shadow-sm ring-1 ring-border'
                : 'text-muted-foreground hover:bg-card/60 hover:text-foreground'}"
        >
            <Icon class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">{label}</span>
        </button>
    {/each}
</div>
