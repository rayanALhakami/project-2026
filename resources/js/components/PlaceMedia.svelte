<script lang="ts">
    import type { Snippet } from 'svelte';
    import CityMotif from '@/components/CityMotif.svelte';
    import { cityTheme } from '@/lib/city-theme';
    import { placeName } from '@/lib/localize';
    import { cn } from '@/lib/utils';
    import type { Place } from '@/types';

    let {
        place,
        class: className = '',
        overlay = false,
        eager = false,
        children,
    }: {
        place: Place;
        class?: string;
        overlay?: boolean;
        eager?: boolean;
        children?: Snippet;
    } = $props();

    const theme = $derived(cityTheme(place.cityId));
    let failed = $state(false);
    const showImage = $derived(Boolean(place.image) && !failed);
</script>

<div
    data-city={place.cityId}
    class={cn('city-tint relative overflow-hidden', className)}
>
    {#if showImage}
        <img
            src={place.image ?? ''}
            alt={placeName(place)}
            class="h-full w-full object-cover"
            loading={eager ? 'eager' : 'lazy'}
            decoding="async"
            onerror={() => (failed = true)}
        />
    {:else}
        <div class="flex h-full w-full items-center justify-center">
            <span class="text-4xl" aria-hidden="true">{theme.emoji}</span>
        </div>
        <div
            class="city-ink pointer-events-none absolute inset-x-0 bottom-0 h-16 opacity-20"
        >
            <CityMotif type={theme.motif} class="h-full w-full" />
        </div>
    {/if}

    {#if overlay}
        <div
            class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/60 via-black/15 to-transparent"
        ></div>
    {/if}

    {@render children?.()}
</div>
