<script lang="ts">
    import Check from '@lucide/svelte/icons/check';
    import ChevronDown from '@lucide/svelte/icons/chevron-down';
    import { onMount, untrack } from 'svelte';
    import {
        Dialog,
        DialogContent,
        DialogDescription,
        DialogTitle,
        DialogTrigger,
    } from '@/components/ui/dialog';
    import { formatNumber, t } from '@/lib/i18n.svelte';
    import { cityName } from '@/lib/localize';
    import { cities } from '@/lib/tourism.svelte';
    import {
        loadAllWeather,
        loadWeather,
        setWeatherCity,
        weatherCityId,
        weatherFor,
    } from '@/lib/weather-city.svelte';

    let open = $state(false);

    const currentCity = $derived(
        cities.find((city) => city.id === weatherCityId()) ?? cities[0],
    );

    onMount(() => {
        void loadAllWeather();
    });

    $effect(() => {
        if (open) {
            untrack(() => void loadAllWeather());
        }
    });

    function choose(id: number): void {
        setWeatherCity(id);
        void loadWeather(id);
        open = false;
    }
</script>

<Dialog bind:open>
    <DialogTrigger>
        {#snippet child({ props })}
            <button
                type="button"
                {...props}
                aria-label={t('weather.changeCity')}
                title={t('weather.changeCity')}
                class="inline-flex min-h-11 items-center gap-2 rounded-full border border-white/20 bg-white/15 px-4 text-lg font-semibold text-white backdrop-blur-sm transition hover:bg-white/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white active:scale-[0.98]"
            >
                {cityName(currentCity)}
                <ChevronDown class="size-4" aria-hidden="true" />
            </button>
        {/snippet}
    </DialogTrigger>

    <DialogContent class="sm:max-w-lg">
        <DialogTitle class="text-xl font-semibold">
            {t('weather.pickCity')}
        </DialogTitle>
        <DialogDescription>{t('weather.pickCityHint')}</DialogDescription>

        <div class="grid grid-cols-2 gap-3">
            {#each cities as city (city.id)}
                {@const weather = weatherFor(city.id)}
                {@const isCurrent = city.id === weatherCityId()}
                <button
                    type="button"
                    onclick={() => choose(city.id)}
                    aria-pressed={isCurrent}
                    class="flex items-center gap-3 rounded-2xl border-2 p-3 text-start transition {isCurrent
                        ? 'border-primary bg-primary/5'
                        : 'border-border bg-card hover:border-primary/50'}"
                >
                    {#if weather}
                        <span
                            data-city={city.id}
                            class="city-tint flex size-12 shrink-0 items-center justify-center rounded-full text-2xl"
                            aria-hidden="true"
                        >
                            {weather.icon}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span
                                class="block truncate text-base font-semibold text-foreground"
                            >
                                {cityName(city)}
                            </span>
                            <span
                                class="block truncate text-base text-muted-foreground"
                            >
                                {formatNumber(weather.temperature)}° · {city.nameEn}
                            </span>
                        </span>
                    {:else}
                        <span
                            class="size-12 shrink-0 animate-pulse rounded-full bg-muted"
                            aria-hidden="true"
                        ></span>
                        <span class="min-w-0 flex-1" aria-hidden="true">
                            <span
                                class="block h-4 w-24 animate-pulse rounded-full bg-muted"
                            ></span>
                            <span
                                class="mt-2 block h-4 w-16 animate-pulse rounded-full bg-muted"
                            ></span>
                        </span>
                    {/if}
                    {#if isCurrent}
                        <Check class="size-5 shrink-0 text-primary" aria-hidden="true" />
                    {/if}
                </button>
            {/each}
        </div>
    </DialogContent>
</Dialog>
