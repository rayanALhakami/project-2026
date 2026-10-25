<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import { t } from '@/lib/i18n.svelte';

    let {
        title = '',
        description = '',
        children,
    }: {
        title?: string;
        description?: string;
        children?: Snippet;
    } = $props();

    const appName = $derived(t('app.name'));
    const fullTitle = $derived(title ? `${title} - ${appName}` : appName);
    const metaDescription = $derived(description || t('meta.description'));
    const canonicalUrl = $derived.by((): string => {
        if (typeof window === 'undefined') {
            return '';
        }

        return new URL(page.url, window.location.origin).href;
    });
</script>

<svelte:head>
    <title>{fullTitle}</title>
    <meta name="description" content={metaDescription} />
    <meta property="og:site_name" content={appName} />
    <meta property="og:type" content="website" />
    <meta property="og:title" content={fullTitle} />
    <meta property="og:description" content={metaDescription} />
    {#if canonicalUrl !== ''}
        <meta property="og:url" content={canonicalUrl} />
        <link rel="canonical" href={canonicalUrl} />
    {/if}
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" content={fullTitle} />
    <meta name="twitter:description" content={metaDescription} />
    {@render children?.()}
</svelte:head>
