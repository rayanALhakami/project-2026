<script lang="ts">
    import type { Snippet } from 'svelte';
    import CityMotif from '@/components/CityMotif.svelte';
    import { cityTheme } from '@/lib/city-theme';
    import { cn } from '@/lib/utils';

    let {
        cityId,
        class: className = '',
        motifClass = 'h-24',
        children,
    }: {
        cityId: number;
        class?: string;
        motifClass?: string;
        children?: Snippet;
    } = $props();

    const theme = $derived(cityTheme(cityId));
</script>

<div
    data-city={cityId}
    class={cn('city-tint relative overflow-hidden', className)}
>
    <div
        class={cn(
            'city-ink pointer-events-none absolute inset-x-0 bottom-0 opacity-20',
            motifClass,
        )}
    >
        <CityMotif type={theme.motif} class="h-full w-full" />
    </div>
    <div class="relative flex h-full flex-col">
        {@render children?.()}
    </div>
</div>
