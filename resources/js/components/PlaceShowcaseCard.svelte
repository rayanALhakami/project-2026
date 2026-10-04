<script lang="ts">
    import Check from '@lucide/svelte/icons/check';
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import Heart from '@lucide/svelte/icons/heart';
    import Plus from '@lucide/svelte/icons/plus';
    import Star from '@lucide/svelte/icons/star';
    import { toast } from 'svelte-sonner';
    import PlaceMedia from '@/components/PlaceMedia.svelte';
    import TrustBadges from '@/components/TrustBadges.svelte';
    import { isFavorite, toggleFavorite } from '@/lib/favorites.svelte';
    import { formatNumber, t } from '@/lib/i18n.svelte';
    import { cityName, placeDescription, placeName } from '@/lib/localize';
    import { categoryMeta, cityById } from '@/lib/tourism.svelte';
    import { openPlaceSheet } from '@/lib/place-sheet.svelte';
    import { isInTripDraft, toggleTripDraft } from '@/lib/trip-draft.svelte';
    import type { Place } from '@/types';

    let {
        place,
        distanceKm = null,
    }: { place: Place; distanceKm?: number | null } = $props();

    const city = $derived(cityById(place.cityId));
    const saved = $derived(isFavorite(place.id));
    const inTrip = $derived(isInTripDraft(place.id));

    function priceLabel(): string {
        if (place.ticketPrice === null) {
            return t('common.perRequest');
        }

        if (place.ticketPrice === 0) {
            return t('common.free');
        }

        return `${formatNumber(place.ticketPrice)} ${t('common.currency')}`;
    }

    function onSave(event: MouseEvent): void {
        event.stopPropagation();
        const nowSaved = toggleFavorite(place.id);
        toast.success(nowSaved ? t('common.saved') : t('common.save'));
    }

    function onAdd(event: MouseEvent): void {
        event.stopPropagation();
        const nowAdded = toggleTripDraft(place.id);
        toast.success(
            nowAdded ? t('common.addedToTrip') : t('common.addToTrip'),
        );
    }
</script>

<!-- svelte-ignore a11y_no_noninteractive_element_to_interactive_role -->
<article
    class="group flex cursor-pointer flex-col overflow-hidden rounded-[20px] bg-card shadow-sm ring-1 ring-border transition hover:-translate-y-1 hover:shadow-lg"
    role="button"
    tabindex="0"
    aria-label={t('place.openDetails', { name: placeName(place) })}
    onclick={() => openPlaceSheet(place)}
    onkeydown={(event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openPlaceSheet(place);
        }
    }}
>
    <div class="relative h-44 shrink-0 overflow-hidden">
        <PlaceMedia
            place={place}
            class="h-full w-full transition duration-500 group-hover:scale-105"
        />
        <span
            class="absolute top-3 end-3 inline-flex items-center gap-1 rounded-full bg-card/95 px-2.5 py-1 text-xs font-bold text-foreground"
        >
            <Star class="size-3.5 fill-amber-400 text-amber-400" />
            {formatNumber(place.rating)}
        </span>
        {#if distanceKm !== null}
            <span
                class="absolute top-3 start-3 rounded-full bg-[#08131f]/80 px-2.5 py-1 text-xs font-bold text-white backdrop-blur"
            >
                {formatNumber(Math.round(distanceKm))}
                {t('places.km')}
            </span>
        {/if}
        <TrustBadges
            {place}
            tone="overlay"
            class="pointer-events-none absolute inset-x-2 bottom-2"
        />
    </div>

    <div class="flex flex-1 flex-col p-4">
        <p class="text-xs font-bold text-emerald-700 dark:text-emerald-400">
            <span aria-hidden="true">{categoryMeta[place.category].icon}</span>
            {t(`categories.${place.category}`)}
            {#if city}
                · {cityName(city)}
            {/if}
        </p>
        <h3 class="mt-1.5 text-base font-bold text-foreground">
            {placeName(place)}
        </h3>
        <p class="mt-1 line-clamp-2 flex-1 text-sm text-muted-foreground">
            {placeDescription(place)}
        </p>

        <div
            class="mt-4 flex items-center justify-between gap-2 border-t border-border pt-3"
        >
            <span class="text-sm font-bold text-foreground">{priceLabel()}</span>

            <div class="flex items-center gap-1.5">
                <button
                    type="button"
                    onclick={onSave}
                    title={saved ? t('common.saved') : t('common.save')}
                    aria-label={saved ? t('common.saved') : t('common.save')}
                    class="inline-flex size-9 items-center justify-center rounded-full ring-1 transition {saved
                        ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 ring-emerald-200 dark:ring-emerald-800'
                        : 'text-muted-foreground ring-border hover:bg-muted hover:text-foreground'}"
                >
                    {#if saved}
                        <Check class="size-4" />
                    {:else}
                        <Heart class="size-4" />
                    {/if}
                </button>
                <button
                    type="button"
                    onclick={onAdd}
                    title={inTrip ? t('common.addedToTrip') : t('common.addToTrip')}
                    aria-label={inTrip
                        ? t('common.addedToTrip')
                        : t('common.addToTrip')}
                    class="inline-flex size-9 items-center justify-center rounded-full ring-1 transition {inTrip
                        ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 ring-emerald-200 dark:ring-emerald-800'
                        : 'text-muted-foreground ring-border hover:bg-muted hover:text-foreground'}"
                >
                    {#if inTrip}
                        <Check class="size-4" />
                    {:else}
                        <Plus class="size-4" />
                    {/if}
                </button>
                <span
                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 px-3 py-2 text-xs font-bold text-emerald-800 dark:text-emerald-300"
                >
                    {t('preview.placeDetails')}
                    <ChevronLeft class="size-3.5 ltr:rotate-180" />
                </span>
            </div>
        </div>
    </div>
</article>
