<script lang="ts">
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import Star from '@lucide/svelte/icons/star';
    import PlaceMedia from '@/components/PlaceMedia.svelte';
    import { formatNumber, t } from '@/lib/i18n.svelte';
    import { placeName } from '@/lib/localize';
    import { openPlaceSheet } from '@/lib/place-sheet.svelte';
    import type { Place } from '@/types';

    let { place }: { place: Place } = $props();

    function priceLabel(): string {
        if (place.ticketPrice === null) {
            return t('common.perRequest');
        }

        if (place.ticketPrice === 0) {
            return t('common.free');
        }

        return `${formatNumber(place.ticketPrice)} ${t('common.currency')}`;
    }
</script>

<button
    type="button"
    onclick={() => openPlaceSheet(place)}
    class="flex w-full items-center gap-3 rounded-xl bg-card p-2 text-start ring-1 ring-border transition hover:ring-emerald-300"
>
    <PlaceMedia place={place} class="size-16 shrink-0 rounded-lg" />
    <span class="min-w-0 flex-1">
        <span class="block truncate font-bold text-foreground">
            {placeName(place)}
        </span>
        <span class="mt-0.5 flex flex-wrap items-center gap-x-2 text-sm text-muted-foreground">
            <span class="inline-flex items-center gap-1">
                <Star class="size-3.5 fill-amber-400 text-amber-400" />
                {formatNumber(place.rating)}
            </span>
            <span aria-hidden="true">·</span>
            <span class="font-bold text-foreground">{priceLabel()}</span>
        </span>
    </span>
    <ChevronLeft
        class="size-5 shrink-0 text-muted-foreground ltr:rotate-180"
        aria-hidden="true"
    />
</button>
