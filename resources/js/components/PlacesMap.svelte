<script lang="ts">
    import type {
        DivIcon,
        LatLngTuple,
        LayerGroup,
        Map as LeafletMap,
    } from 'leaflet';
    import { onDestroy, onMount } from 'svelte';
    import { formatNumber, t } from '@/lib/i18n.svelte';
    import { placeName } from '@/lib/localize';
    import { categoryMeta } from '@/lib/tourism.svelte';
    import { cn } from '@/lib/utils';
    import type { Place } from '@/types';

    type LeafletStatic = typeof import('leaflet');

    let {
        places,
        onSelect,
        class: className = '',
    }: {
        places: Place[];
        onSelect?: (place: Place) => void;
        class?: string;
    } = $props();

    const SAUDI_CENTER: LatLngTuple = [23.8859, 45.0792];
    const TILE_URL = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    const ATTRIBUTION =
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

    let container = $state<HTMLDivElement | null>(null);
    let ready = $state(false);
    let failed = $state(false);

    let leaflet: LeafletStatic | null = null;
    let map: LeafletMap | null = null;
    let markerLayer: LayerGroup | null = null;
    let observer: ResizeObserver | null = null;
    let fitTimer: ReturnType<typeof setTimeout> | null = null;

    function escapeHtml(value: string): string {
        return value
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }

    function priceLabel(place: Place): string {
        if (place.ticketPrice === null) {
            return t('common.perRequest');
        }

        if (place.ticketPrice === 0) {
            return t('common.free');
        }

        return `${formatNumber(place.ticketPrice)} ${t('common.currency')}`;
    }

    function categoryColor(place: Place): string {
        return categoryMeta[place.category]?.color ?? '#0b1e33';
    }

    function pinIcon(place: Place): DivIcon {
        return leaflet!.divIcon({
            className: 'places-map-marker',
            html: `<span class="places-map-pin" style="background-color:${categoryColor(place)}"></span>`,
            iconSize: [26, 26],
            iconAnchor: [13, 13],
            popupAnchor: [0, -14],
        });
    }

    function popupHtml(place: Place): string {
        const name = escapeHtml(placeName(place));
        const category = `${categoryMeta[place.category]?.icon ?? ''} ${escapeHtml(
            t(`categories.${place.category}`),
        )}`;

        return `<div dir="auto" class="places-map-popup">
            <p class="places-map-popup-name">${name}</p>
            <p class="places-map-popup-meta">${category}</p>
            <p class="places-map-popup-price">${escapeHtml(priceLabel(place))}</p>
        </div>`;
    }

    function updateMarkers(list: Place[]): void {
        if (!leaflet || !map || !markerLayer) {
            return;
        }

        markerLayer.clearLayers();

        const points: LatLngTuple[] = [];

        list.forEach((place) => {
            const point: LatLngTuple = [place.latitude, place.longitude];
            points.push(point);

            leaflet!
                .marker(point, {
                    icon: pinIcon(place),
                    keyboard: false,
                    title: placeName(place),
                })
                .bindPopup(popupHtml(place), { closeButton: true })
                .on('click', () => onSelect?.(place))
                .addTo(markerLayer!);
        });

        if (points.length === 1) {
            map.setView(points[0], 13);
        } else if (points.length > 1) {
            map.fitBounds(leaflet.latLngBounds(points), {
                padding: [36, 36],
                maxZoom: 14,
            });
        } else {
            map.setView(SAUDI_CENTER, 5);
        }

        window.setTimeout(() => map?.invalidateSize(), 0);
    }

    $effect(() => {
        const list = places;

        if (!ready) {
            return;
        }

        if (fitTimer !== null) {
            clearTimeout(fitTimer);
        }

        fitTimer = setTimeout(() => {
            fitTimer = null;
            updateMarkers(list);
        }, 150);

        return () => {
            if (fitTimer !== null) {
                clearTimeout(fitTimer);
                fitTimer = null;
            }
        };
    });

    onMount(() => {
        let cancelled = false;

        void (async () => {
            try {
                const module = (await import('leaflet')) as LeafletStatic & {
                    default?: LeafletStatic;
                };
                const instance = module.default ?? module;

                await import('leaflet/dist/leaflet.css');

                if (cancelled || !container) {
                    return;
                }

                leaflet = instance;
                map = instance.map(container, {
                    zoomControl: true,
                    scrollWheelZoom: true,
                });
                instance
                    .tileLayer(TILE_URL, {
                        maxZoom: 19,
                        attribution: ATTRIBUTION,
                    })
                    .addTo(map);
                markerLayer = instance.layerGroup().addTo(map);
                map.setView(SAUDI_CENTER, 5);
                ready = true;
                observer = new ResizeObserver(() => map?.invalidateSize());
                observer.observe(container);
            } catch {
                failed = true;
            }
        })();

        return () => {
            cancelled = true;
        };
    });

    onDestroy(() => {
        if (fitTimer !== null) {
            clearTimeout(fitTimer);
            fitTimer = null;
        }

        observer?.disconnect();
        observer = null;
        map?.remove();
        map = null;
        markerLayer = null;
        leaflet = null;
    });
</script>

<div
    class={cn(
        'relative h-[26rem] w-full overflow-hidden rounded-[20px] bg-muted ring-1 ring-border sm:h-[32rem]',
        className,
    )}
    role="region"
    aria-label={t('places.viewMap')}
>
    <div bind:this={container} dir="ltr" class="absolute inset-0 z-0"></div>

    {#if failed}
        <div
            class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-2 bg-muted p-6 text-center"
            role="alert"
        >
            <p class="text-3xl" aria-hidden="true">🗺️</p>
            <p class="text-base font-bold text-muted-foreground">
                {t('places.mapError')}
            </p>
        </div>
    {:else if !ready}
        <div
            class="pointer-events-none absolute inset-0 z-10 flex flex-col items-center justify-center gap-3"
            aria-busy="true"
        >
            <span class="size-10 animate-pulse rounded-full bg-foreground/10"></span>
            <span class="h-3 w-24 animate-pulse rounded-full bg-foreground/10"></span>
        </div>
    {/if}

    {#if ready && !failed}
        <p
            class="pointer-events-none absolute bottom-2 end-2 z-20 rounded-full bg-card/90 px-3 py-1 text-xs font-bold text-muted-foreground shadow-sm"
        >
            {t('places.mapHint')}
        </p>
    {/if}
</div>

<style>
    :global(.places-map-pin) {
        display: block;
        width: 18px;
        height: 18px;
        margin: 4px;
        border: 2.5px solid #fff;
        border-radius: 9999px;
        box-shadow: 0 1px 4px rgb(0 0 0 / 0.45);
    }

    :global(.places-map-popup) {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 10rem;
        font-family: inherit;
    }

    :global(.places-map-popup-name) {
        font-size: 14px;
        font-weight: 700;
    }

    :global(.places-map-popup-meta) {
        font-size: 12px;
        color: #4b5563;
    }

    :global(.places-map-popup-price) {
        font-size: 12px;
        font-weight: 700;
    }
</style>
