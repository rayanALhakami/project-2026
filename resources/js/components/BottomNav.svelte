<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import LayoutGrid from '@lucide/svelte/icons/layout-grid';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Route from '@lucide/svelte/icons/route';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import UserRound from '@lucide/svelte/icons/user-round';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { t } from '@/lib/i18n.svelte';
    import { toUrl } from '@/lib/utils';
    import {
        assistant,
        dashboard,
        home,
        login,
        places,
        trips,
    } from '@/routes';
    import { edit } from '@/routes/profile';
    import type { NavItem } from '@/types';

    type BottomNavItem = NavItem & {
        key: 'home' | 'places' | 'assistant' | 'trips' | 'account';
    };

    const url = currentUrlState();
    const auth = $derived(page.props.auth);

    const items = $derived<BottomNavItem[]>([
        {
            key: 'home',
            title: t('nav.home'),
            href: auth.user ? dashboard() : home(),
            icon: LayoutGrid,
        },
        { key: 'places', title: t('nav.places'), href: places(), icon: MapPin },
        {
            key: 'assistant',
            title: t('nav.assistant'),
            href: assistant(),
            icon: Sparkles,
        },
        { key: 'trips', title: t('nav.tripsShort'), href: trips(), icon: Route },
        auth.user
            ? {
                  key: 'account',
                  title: t('nav.account'),
                  href: edit(),
                  icon: UserRound,
              }
            : {
                  key: 'account',
                  title: t('welcome.login'),
                  href: login(),
                  icon: UserRound,
              },
    ]);
</script>

<nav
    class="pb-safe fixed inset-x-0 bottom-0 z-40 border-t border-border bg-card/95 backdrop-blur md:hidden"
    aria-label={t('nav.menu')}
>
    <ul class="mx-auto flex max-w-xl items-stretch justify-between px-1">
        {#each items as item (item.key)}
            {@const active = url.isCurrentOrParentUrl(
                item.href,
                url.currentUrl,
            )}
            <li class="flex-1">
                {#if item.key === 'assistant'}
                    <Link
                        href={toUrl(item.href)}
                        aria-current={active ? 'page' : undefined}
                        class="group flex min-h-16 flex-col items-center justify-end gap-0.5 pb-1.5"
                    >
                        <span
                            class="flex size-12 -translate-y-4 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-teal-700 text-white shadow-lg shadow-emerald-950/30 ring-4 ring-card transition group-active:scale-95"
                        >
                            <item.icon class="size-6 shrink-0" />
                        </span>
                        <span
                            class="-mt-3 text-xs font-bold {active
                                ? 'text-primary'
                                : 'text-foreground'}"
                        >
                            {item.title}
                        </span>
                    </Link>
                {:else}
                    <Link
                        href={toUrl(item.href)}
                        aria-current={active ? 'page' : undefined}
                        class="flex min-h-16 flex-col items-center justify-center gap-1 rounded-2xl px-1 py-2 text-base font-semibold transition {active
                            ? 'text-primary'
                            : 'text-muted-foreground hover:text-foreground'}"
                    >
                        <item.icon class="size-6 shrink-0" />
                        <span class="text-center leading-tight">
                            {item.title}
                        </span>
                    </Link>
                {/if}
            </li>
        {/each}
    </ul>
</nav>
