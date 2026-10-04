<script lang="ts">
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { getLocale, t } from '@/lib/i18n.svelte';

    const name = $derived(t('app.name'));
    const showArabic = $derived(getLocale() !== 'ar');
</script>

<div
    class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
>
    <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
</div>
<div class="ms-1 grid min-w-0 flex-1 text-start text-sm">
    <span class="leading-tight font-semibold break-words">{name}</span>
    {#if showArabic}
        <span
            class="mt-0.5 text-[11px] leading-tight break-words text-muted-foreground"
        >
            {t('app.nameAr')}
        </span>
    {/if}
</div>
