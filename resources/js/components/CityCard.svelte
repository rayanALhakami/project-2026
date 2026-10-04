<script lang="ts">
    import type { LinkComponentBaseProps } from '@inertiajs/core';
    import { Link } from '@inertiajs/svelte';
    import ChevronRight from '@lucide/svelte/icons/chevron-right';
    import CityArt from '@/components/CityArt.svelte';
    import { formatNumber, t } from '@/lib/i18n.svelte';
    import { cityName } from '@/lib/localize';
    import { placesByCity } from '@/lib/tourism.svelte';
    import { toUrl } from '@/lib/utils';
    import type { City } from '@/types';

    let {
        city,
        href,
        selected = false,
        onclick,
    }: {
        city: City;
        href: NonNullable<LinkComponentBaseProps['href']>;
        selected?: boolean;
        onclick?: () => void;
    } = $props();

    const count = $derived(placesByCity(city.id).length);
</script>

<Link
    href={toUrl(href)}
    {onclick}
    class="group block overflow-hidden rounded-[18px] border bg-card transition hover:-translate-y-0.5 {selected
        ? 'border-primary'
        : 'border-border'}"
>
    <CityArt cityId={city.id} image={city.image} class="h-32" motifClass="h-24">
        <div class="flex h-full flex-col justify-between p-4">
            <span
                class="self-start rounded-full bg-card/85 px-3 py-1 text-sm font-semibold text-foreground"
            >
                {t('places.count', { count: formatNumber(count) })}
            </span>
            <div class="flex items-end justify-between gap-2">
                <div>
                    <p class="text-xl font-semibold text-foreground">
                        {cityName(city)}
                    </p>
                    <p class="text-sm text-muted-foreground">{city.nameEn}</p>
                </div>
                <span
                    class="inline-flex items-center gap-1 rounded-full bg-primary px-3 py-1.5 text-sm font-semibold text-primary-foreground"
                >
                    {t('common.explore')}
                    <ChevronRight class="size-4 rtl:rotate-180" />
                </span>
            </div>
        </div>
    </CityArt>
</Link>
