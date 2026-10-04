<script lang="ts">
    import SaudiFlag from '@/components/SaudiFlag.svelte';
    import { t } from '@/lib/i18n.svelte';
</script>

<footer class="mt-auto flex flex-col items-center gap-2 px-4 py-8 text-center">
    <SaudiFlag class="h-9 w-[3.375rem]" />
    <p class="text-sm font-semibold text-muted-foreground">{t('app.name')}</p>
</footer>
