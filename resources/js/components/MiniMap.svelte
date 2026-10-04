<script lang="ts">
    import type {
        DivIcon,
        LatLngTuple,
        LayerGroup,
        Map as LeafletMap,
    } from 'leaflet';
    import { onDestroy, onMount } from 'svelte';
    import { t } from '@/lib/i18n.svelte';
    import { placeName } from '@/lib/localize';
    import { cn } from '@/lib/utils';
    import type { Place } from '@/types';

    type LeafletStatic = typeof import('leaflet');

    let {
        places,
        class: className = '',
        label,
    }: {
        places: Place[];
        class?: string;
        label?: string;
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

    interface FallbackPin {
        id: number;
        x: number;
        y: number;
        order: number;
    }

    const fallbackPins = $derived.by((): FallbackPin[] => {
        if (places.length === 0) {
            return [];
        }

        if (places.length === 1) {
            const only = places[0];

            return [{ id: only.id, order: 1, x: 50, y: 50 }];
        }

        const latitudes = places.map((place) => place.latitude);
        const longitudes = places.map((place) => place.longitude);
        const minLat = Math.min(...latitudes);
        const maxLat = Math.max(...latitudes);
        const minLon = Math.min(...longitudes);
        const maxLon = Math.max(...longitudes);
        const latSpan = maxLat - minLat || 1;
        const lonSpan = maxLon - minLon || 1;

        return places.map((place, index) => ({
            id: place.id,
            order: index + 1,
            x: 12 + ((place.longitude - minLon) / lonSpan) * 76,
            y: 88 - ((place.latitude - minLat) / latSpan) * 76,
        }));
    });

    const fallbackPath = $derived(
        fallbackPins.map((pin) => `${pin.x},${pin.y}`).join(' '),
    );

    function escapeHtml(value: string): string {
        return value
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }

    function numberedIcon(order: number): DivIcon {
        return leaflet!.divIcon({
            className: 'mini-map-marker',
            html: `<span class="mini-map-pin">${order}</span>`,
            iconSize: [28, 28],
            iconAnchor: [14, 14],
            popupAnchor: [0, -16],
        });
    }

    function updateMarkers(list: Place[]): void {
        if (!leaflet || !map || !markerLayer) {
            return;
        }

        markerLayer.clearLayers();

        const points: LatLngTuple[] = [];

        list.forEach((place, index) => {
            const point: LatLngTuple = [place.latitude, place.longitude];
            points.push(point);

            leaflet!
                .marker(point, {
                    icon: numberedIcon(index + 1),
                    keyboard: false,
                    title: placeName(place),
                })
                .bindPopup(
                    `<span dir="auto">${escapeHtml(placeName(place))}</span>`,
                )
                .addTo(markerLayer!);
        });

        if (points.length === 1) {
            map.setView(points[0], 14);
        } else if (points.length > 1) {
            map.fitBounds(leaflet.latLngBounds(points), {
                padding: [28, 28],
                maxZoom: 15,
            });
        } else {
            map.setView(SAUDI_CENTER, 5);
        }

        window.setTimeout(() => map?.invalidateSize(), 0);
    }

    $effect(() => {
        if (!ready) {
            return;
        }

        updateMarkers(places);
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
                    zoomControl: false,
                    scrollWheelZoom: false,
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
        'relative h-40 overflow-hidden rounded-2xl bg-muted ring-1 ring-border',
        className,
    )}
    role="img"
    aria-label={label ?? t('place.miniMap')}
>
    <div bind:this={container} dir="ltr" class="absolute inset-0 z-0"></div>

    {#if failed}
        <div class="absolute inset-0 z-10">
            <div
                class="absolute inset-0 opacity-80"
                style="background-image: linear-gradient(var(--border) 1px, transparent 1px), linear-gradient(90deg, var(--border) 1px, transparent 1px); background-size: 28px 28px;"
            ></div>

            {#if fallbackPins.length > 1}
                <svg
                    class="absolute inset-0 h-full w-full"
                    viewBox="0 0 100 100"
                    preserveAspectRatio="none"
                    aria-hidden="true"
                >
                    <polyline
                        points={fallbackPath}
                        fill="none"
                        stroke="#10b981"
                        stroke-width="1"
                        stroke-dasharray="3 3"
                        vector-effect="non-scaling-stroke"
                    />
                </svg>
            {/if}

            {#each fallbackPins as pin (pin.id)}
                <span
                    class="absolute flex size-7 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border-2 border-white bg-[#0b1e33] text-xs font-bold text-white shadow"
                    style="left: {pin.x}%; top: {pin.y}%;"
                >
                    {pin.order}
                </span>
            {/each}
        </div>
    {:else if !ready}
        <div
            class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center"
            aria-hidden="true"
        >
            <span class="size-8 animate-pulse rounded-full bg-foreground/10"></span>
        </div>
    {/if}

    <span
        class="pointer-events-none absolute top-1.5 end-2 z-20 rounded-full bg-card/90 px-2 py-0.5 text-[10px] font-bold text-foreground"
    >
        {label ?? t('place.miniMap')}
    </span>
</div>

<style>
    :global(.mini-map-pin) {
        display: flex;
        width: 100%;
        height: 100%;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        border-radius: 9999px;
        background-color: #0b1e33;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        box-shadow: 0 1px 4px rgb(0 0 0 / 0.4);
    }
</style>
