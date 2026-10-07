<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Compass from '@lucide/svelte/icons/compass';
    import Heart from '@lucide/svelte/icons/heart';
    import AppHead from '@/components/AppHead.svelte';
    import PlaceCardSkeleton from '@/components/PlaceCardSkeleton.svelte';
    import PlaceDetailsSheet from '@/components/PlaceDetailsSheet.svelte';
    import PlaceShowcaseCard from '@/components/PlaceShowcaseCard.svelte';
    import SiteHeader from '@/components/SiteHeader.svelte';
    import { Toaster } from '@/components/ui/sonner';
    import { favoriteIds, favoritesHydrated } from '@/lib/favorites.svelte';
    import { formatNumber, t } from '@/lib/i18n.svelte';
    import { places } from '@/lib/tourism.svelte';
    import { places as placesRoute } from '@/routes';

    const favoritePlaces = $derived.by(() => {
        const ids = favoriteIds();

        return places.filter((place) => ids.includes(place.id));
    });
</script>

<AppHead title={t('favorites.title')} />

<SiteHeader active="favorites" transparent />

<div class="min-h-dvh bg-background">
    <section
        class="bg-gradient-to-br from-[#16344f] via-[#0b2337] to-[#071523] pb-16 pt-28 text-white"
    >
        <div class="mx-auto w-full max-w-6xl px-4 md:px-6">
            <span
                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-emerald-200 ring-1 ring-white/15"
            >
                <Heart class="size-4" aria-hidden="true" />
                {t('nav.favorites')}
            </span>
            <h1 class="mt-4 text-3xl font-bold sm:text-4xl">
                {t('favorites.title')}
            </h1>
            <p class="mt-2 max-w-2xl text-slate-300">
                {t('favorites.subtitle')}
            </p>
        </div>
    </section>

    <div class="mx-auto -mt-8 w-full max-w-6xl px-4 pb-20 md:px-6">
        {#if !favoritesHydrated()}
            <div
                class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
                aria-busy="true"
                aria-live="polite"
            >
                {#each Array.from({ length: 3 }) as _, index (index)}
                    <PlaceCardSkeleton />
                {/each}
            </div>
        {:else if favoritePlaces.length === 0}
            <div
                class="rounded-[20px] border-2 border-dashed border-border bg-card p-10 text-center sm:p-12"
            >
                <span
                    class="mx-auto flex size-14 items-center justify-center rounded-full bg-rose-50 text-rose-500 dark:bg-rose-950/40 dark:text-rose-300"
                >
                    <Heart class="size-7" aria-hidden="true" />
                </span>
                <p class="mt-4 text-lg font-bold text-foreground">
                    {t('favorites.empty')}
                </p>
                <p class="mt-1 text-base text-muted-foreground">
                    {t('favorites.emptyHint')}
                </p>
                <Link
                    href={placesRoute().url}
                    class="mt-6 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700 px-6 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:brightness-110 active:scale-[0.98]"
                >
                    <Compass class="size-4" aria-hidden="true" />
                    {t('favorites.browse')}
                </Link>
            </div>
        {:else}
            <div
                class="flex flex-wrap items-center justify-between gap-3 rounded-[20px] bg-card p-5 shadow-lg shadow-slate-900/5 ring-1 ring-border"
            >
                <p class="text-sm font-bold text-muted-foreground">
                    {t('favorites.count', {
                        count: formatNumber(favoritePlaces.length),
                    })}
                </p>
                <Link
                    href={placesRoute().url}
                    class="inline-flex items-center gap-1 text-sm font-bold text-emerald-700 transition hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300"
                >
                    {t('favorites.browse')}
                    <Compass class="size-4" aria-hidden="true" />
                </Link>
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                {#each favoritePlaces as place (place.id)}
                    <PlaceShowcaseCard {place} />
                {/each}
            </div>
        {/if}
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

<PlaceDetailsSheet />
<Toaster />
