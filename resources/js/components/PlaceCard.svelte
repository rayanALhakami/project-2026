<script lang="ts">
    import Check from '@lucide/svelte/icons/check';
    import Clock from '@lucide/svelte/icons/clock';
    import Heart from '@lucide/svelte/icons/heart';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Plus from '@lucide/svelte/icons/plus';
    import Star from '@lucide/svelte/icons/star';
    import { toast } from 'svelte-sonner';
    import ActionButton from '@/components/ActionButton.svelte';
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

    const meta = $derived(categoryMeta[place.category]);
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
    class="group flex cursor-pointer flex-col gap-3 rounded-[18px] border border-border bg-card p-4 transition hover:border-primary/40"
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
    <div class="relative">
        <PlaceMedia place={place} class="h-36 w-full rounded-2xl" />
        <div
            class="pointer-events-none absolute inset-x-2 top-2 flex items-start justify-between gap-2"
        >
            <span
                class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-black/35 px-3 py-1 text-sm font-semibold text-white backdrop-blur"
            >
                <span aria-hidden="true">{meta.icon}</span>
                {t(`categories.${place.category}`)}
            </span>
            {#if distanceKm !== null}
                <span
                    class="inline-flex items-center gap-1 rounded-full border border-white/25 bg-black/35 px-3 py-1 text-sm font-semibold text-white backdrop-blur"
                >
                    {formatNumber(Math.round(distanceKm))} {t('places.km')}
                </span>
            {/if}
        </div>
        <TrustBadges
            {place}
            tone="overlay"
            class="pointer-events-none absolute inset-x-2 bottom-2"
        />
    </div>

    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <h3 class="text-lg font-semibold text-foreground">{placeName(place)}</h3>
            <p
                class="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground"
            >
                <MapPin class="size-4 shrink-0" aria-hidden="true" />
                {#if city}{cityName(city)}{/if}
            </p>
        </div>
        <span class="inline-flex items-center gap-1 text-sm font-semibold">
            <Star class="size-4 fill-amber-400 text-amber-400" />
            {formatNumber(place.rating)}
        </span>
    </div>

    <p class="line-clamp-2 text-sm leading-relaxed text-muted-foreground">
        {placeDescription(place)}
    </p>

    <div
        class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground"
    >
        <span class="inline-flex items-center gap-1.5">
            <Clock class="size-4 shrink-0" aria-hidden="true" />
            {t(`time.${place.bestTime}`)}
        </span>
        <span class="font-semibold text-foreground">{priceLabel()}</span>
    </div>

    <div class="mt-1 grid grid-cols-2 gap-2">
        <ActionButton
            variant={saved ? 'secondary' : 'outline'}
            onclick={onSave}
            class="w-full"
        >
            {#if saved}
                <Check class="size-4" aria-hidden="true" />
            {:else}
                <Heart class="size-4" aria-hidden="true" />
            {/if}
            <span>{saved ? t('common.saved') : t('common.save')}</span>
        </ActionButton>

        <ActionButton
            variant={inTrip ? 'secondary' : 'outline'}
            onclick={onAdd}
            class="w-full"
        >
            {#if inTrip}
                <Check class="size-4" aria-hidden="true" />
            {:else}
                <Plus class="size-4" aria-hidden="true" />
            {/if}
            <span>{inTrip ? t('common.addedToTrip') : t('common.addToTrip')}</span>
        </ActionButton>
    </div>
</article>
