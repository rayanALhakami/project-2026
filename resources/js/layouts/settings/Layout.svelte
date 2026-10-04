<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import SettingsIcon from '@lucide/svelte/icons/settings';
    import type { Snippet } from 'svelte';
    import SiteHeader from '@/components/SiteHeader.svelte';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { t } from '@/lib/i18n.svelte';
    import { toUrl } from '@/lib/utils';
    import { edit as editAppearance } from '@/routes/appearance';
    import { edit as editProfile } from '@/routes/profile';
    import { edit as editSecurity } from '@/routes/security';
    import type { NavItem } from '@/types';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const url = currentUrlState();

    const navItems = $derived<NavItem[]>([
        { title: t('settings.profile'), href: editProfile() },
        { title: t('settings.security'), href: editSecurity() },
        { title: t('settings.appearance'), href: editAppearance() },
    ]);
</script>

<SiteHeader />

<div class="min-h-dvh bg-background">
    <section
        class="bg-gradient-to-br from-[#16344f] via-[#0b2337] to-[#071523] pb-16 pt-28 text-white"
    >
        <div class="mx-auto w-full max-w-4xl px-4 md:px-6">
            <span
                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-emerald-200 ring-1 ring-white/15"
            >
                <SettingsIcon class="size-4" />
                {t('nav.settings')}
            </span>
            <h1 class="mt-4 text-3xl font-bold sm:text-4xl">
                {t('nav.settings')}
            </h1>
            <p class="mt-2 text-slate-300">{t('settings.subtitle')}</p>
        </div>
    </section>

    <div class="mx-auto -mt-8 w-full max-w-4xl px-4 pb-20 md:px-6">
        <nav
            class="flex flex-wrap gap-2 rounded-[18px] bg-card p-2 shadow-lg shadow-slate-900/5 ring-1 ring-border"
            aria-label={t('nav.settings')}
        >
            {#each navItems as item (toUrl(item.href))}
                {@const active = url.isCurrentUrl(item.href, url.currentUrl)}
                <Link
                    href={toUrl(item.href)}
                    class="inline-flex min-h-10 items-center rounded-xl px-4 text-sm font-bold transition {active
                        ? 'bg-[#0b1e33] text-white'
                        : 'text-muted-foreground hover:bg-muted hover:text-foreground'}"
                >
                    {item.title}
                </Link>
            {/each}
        </nav>

        <section class="mt-6 space-y-6">
            {@render children?.()}
        </section>
    </div>

    <footer
        class="border-t border-white/10 bg-[#08131f] pt-6 pb-bottom-nav text-slate-400 md:pb-6"
    >
        <div
            class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 text-xs sm:flex-row md:px-6"
        >
            <p>© {new Date().getFullYear()} {t('app.name')}</p>
            <p>{t('preview.rights')}</p>
        </div>
    </footer>
</div>
