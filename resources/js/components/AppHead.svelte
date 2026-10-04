<script lang="ts">
    import type { Snippet } from 'svelte';
    import { t } from '@/lib/i18n.svelte';

    let {
        title = '',
        children,
    }: {
        title?: string;
        children?: Snippet;
    } = $props();

    const appName = $derived(t('app.name'));
    const fullTitle = $derived(title ? `${title} - ${appName}` : appName);
</script>

<svelte:head>
    <title>{fullTitle}</title>
    {@render children?.()}
</svelte:head>
