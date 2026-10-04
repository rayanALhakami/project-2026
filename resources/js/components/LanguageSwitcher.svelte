<script lang="ts">
    import { page, useHttp } from '@inertiajs/svelte';
    import Languages from '@lucide/svelte/icons/languages';
    import { update } from '@/routes/locale';
    import {
        getLocale,
        locales,
        setLocale,
        t,
        type LocaleCode,
    } from '@/lib/i18n.svelte';

    const http = useHttp({ locale: '' });

    function onChange(event: Event): void {
        const value = (event.currentTarget as HTMLSelectElement)
            .value as LocaleCode;
        setLocale(value);

        if (page.props.auth.user) {
            http.locale = value;
            http.patch(update.url());
        }
    }
</script>

<label class="relative flex items-center">
    <Languages
        class="pointer-events-none absolute start-3 size-5 text-muted-foreground"
    />
    <select
        value={getLocale()}
        onchange={onChange}
        class="appearance-none rounded-full bg-transparent py-2.5 ps-10 pe-3 text-base font-semibold text-foreground transition hover:bg-muted"
        aria-label={t('nav.language')}
    >
        {#each locales as locale (locale.code)}
            <option value={locale.code}>{locale.flag} {locale.label}</option>
        {/each}
    </select>
</label>
