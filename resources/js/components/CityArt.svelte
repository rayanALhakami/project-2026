<script lang="ts">
    import type { Snippet } from 'svelte';
    import CityMotif from '@/components/CityMotif.svelte';
    import { cityTheme } from '@/lib/city-theme';
    import { cn } from '@/lib/utils';

    let {
        cityId,
        image = null,
        class: className = '',
        motifClass = 'h-24',
        children,
    }: {
        cityId: number;
        image?: string | null;
        class?: string;
        motifClass?: string;
        children?: Snippet;
    } = $props();

    const theme = $derived(cityTheme(cityId));
    let failed = $state(false);
    const showsImage = $derived(Boolean(image) && !failed);
</script>

<div
    data-city={cityId}
    class={cn('city-tint relative overflow-hidden', className)}
>
    {#if showsImage}
        <img
            src={image ?? ''}
            alt=""
            class="absolute inset-0 h-full w-full object-cover"
            loading="lazy"
            decoding="async"
            onerror={() => (failed = true)}
        />
        <div
            class="pointer-events-none absolute inset-0 bg-gradient-to-t from-card via-card/40 to-transparent"
        ></div>
    {:else}
        <div
            class={cn(
                'city-ink pointer-events-none absolute inset-x-0 bottom-0 opacity-20',
                motifClass,
            )}
        >
            <CityMotif type={theme.motif} class="h-full w-full" />
        </div>
    {/if}
    <div class="relative flex h-full flex-col">
        {@render children?.()}
    </div>
</div>
