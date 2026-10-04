<script lang="ts">
    import type { Snippet } from 'svelte';
    import { cn } from '@/lib/utils';
    import type { WeatherCondition } from '@/types';

    let {
        condition = 'clear',
        class: className = '',
        children,
    }: {
        condition?: WeatherCondition;
        class?: string;
        children?: Snippet;
    } = $props();

    const drops = Array.from({ length: 16 }, (_, index) => index);

    const isStormy = $derived(
        condition === 'cloudy' ||
            condition === 'rain' ||
            condition === 'thunder',
    );
</script>

{#snippet cloud(cls: string)}
    <svg
        viewBox="0 0 120 56"
        class={cn('weather-cloud', cls)}
        aria-hidden="true"
        focusable="false"
    >
        <g fill="currentColor">
            <ellipse cx="34" cy="38" rx="24" ry="13" />
            <ellipse cx="62" cy="26" rx="24" ry="18" />
            <ellipse cx="90" cy="38" rx="22" ry="13" />
            <rect x="10" y="32" width="100" height="18" rx="9" />
        </g>
    </svg>
{/snippet}

<div
    data-weather={condition}
    class={cn(
        'weather-sky relative flex min-h-[17rem] flex-col overflow-hidden',
        className,
    )}
>
    <div class="weather-sun" aria-hidden="true"></div>
    <div class="weather-moon" aria-hidden="true"></div>

    <div class="weather-stars" aria-hidden="true">
        <span class="weather-star"></span>
        <span class="weather-star"></span>
        <span class="weather-star"></span>
        <span class="weather-star"></span>
    </div>

    <div class="weather-clouds" aria-hidden="true">
        {#if condition === 'partly-cloudy'}
            {@render cloud('start-[-2.5rem] bottom-[-1.25rem] w-36 opacity-60')}
            {@render cloud('end-[-2.5rem] bottom-[2rem] w-28 opacity-40')}
        {:else if isStormy}
            {@render cloud('start-[-3rem] top-[4%] w-40 opacity-50')}
            {@render cloud('end-[-2rem] top-[16%] w-36 opacity-45')}
            {@render cloud('start-[22%] bottom-[2%] w-32 opacity-35')}
            {@render cloud('end-[16%] bottom-[-1.5rem] w-36 opacity-40')}
        {/if}
    </div>

    {#if condition === 'rain' || condition === 'thunder'}
        <div class="weather-rain" aria-hidden="true">
            {#each drops as drop (drop)}
                <span style="--i: {drop}"></span>
            {/each}
        </div>
    {/if}

    {#if condition === 'thunder'}
        <div class="weather-lightning" aria-hidden="true"></div>
    {/if}

    {#if condition === 'fog'}
        <div class="weather-fog" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </div>
    {/if}

    {#if condition === 'dust'}
        <div class="weather-dust" aria-hidden="true">
            <span></span>
            <span></span>
        </div>
    {/if}

    {#if condition === 'hot'}
        <div class="weather-heat" aria-hidden="true"></div>
    {/if}

    <div class="relative z-10 flex flex-1 flex-col">
        {@render children?.()}
    </div>
</div>
