<script lang="ts">
    import type { WeatherCondition } from '@/types';

    let {
        condition = 'clear',
        class: className = 'size-12',
    }: {
        condition?: WeatherCondition;
        class?: string;
    } = $props();

    const uid = $props.id();

    const sun = `url(#w-sun-${uid})`;
    const cloud = `url(#w-cloud-${uid})`;
    const drop = `url(#w-drop-${uid})`;
    const bolt = `url(#w-bolt-${uid})`;
    const dust = `url(#w-dust-${uid})`;
    const hot = `url(#w-hot-${uid})`;
</script>

<svg viewBox="0 0 64 64" class={className} aria-hidden="true" focusable="false">
    <defs>
        <linearGradient
            id={`w-sun-${uid}`}
            x1="20"
            y1="14"
            x2="46"
            y2="50"
            gradientUnits="userSpaceOnUse"
        >
            <stop offset="0" stop-color="#FFD60A" />
            <stop offset="1" stop-color="#FF9F0A" />
        </linearGradient>
        <linearGradient
            id={`w-cloud-${uid}`}
            x1="14"
            y1="14"
            x2="52"
            y2="46"
            gradientUnits="userSpaceOnUse"
        >
            <stop offset="0" stop-color="#FFFFFF" />
            <stop offset="1" stop-color="#B4C2D6" />
        </linearGradient>
        <linearGradient
            id={`w-drop-${uid}`}
            x1="18"
            y1="44"
            x2="46"
            y2="58"
            gradientUnits="userSpaceOnUse"
        >
            <stop offset="0" stop-color="#5AC8FA" />
            <stop offset="1" stop-color="#0A84FF" />
        </linearGradient>
        <linearGradient
            id={`w-bolt-${uid}`}
            x1="24"
            y1="39"
            x2="40"
            y2="63"
            gradientUnits="userSpaceOnUse"
        >
            <stop offset="0" stop-color="#FFD60A" />
            <stop offset="1" stop-color="#FF9F0A" />
        </linearGradient>
        <linearGradient
            id={`w-dust-${uid}`}
            x1="16"
            y1="18"
            x2="48"
            y2="58"
            gradientUnits="userSpaceOnUse"
        >
            <stop offset="0" stop-color="#E9CF9C" />
            <stop offset="1" stop-color="#C9A15E" />
        </linearGradient>
        <linearGradient
            id={`w-hot-${uid}`}
            x1="20"
            y1="18"
            x2="48"
            y2="58"
            gradientUnits="userSpaceOnUse"
        >
            <stop offset="0" stop-color="#FFB340" />
            <stop offset="1" stop-color="#FF453A" />
        </linearGradient>
    </defs>

    {#if condition === 'clear'}
        <g stroke={sun} stroke-width="4" stroke-linecap="round">
            <line x1="32" y1="7" x2="32" y2="15" />
            <line x1="44" y1="20" x2="49.7" y2="14.3" />
            <line x1="49" y1="32" x2="57" y2="32" />
            <line x1="44" y1="44" x2="49.7" y2="49.7" />
            <line x1="32" y1="49" x2="32" y2="57" />
            <line x1="20" y1="44" x2="14.3" y2="49.7" />
            <line x1="15" y1="32" x2="7" y2="32" />
            <line x1="20" y1="20" x2="14.3" y2="14.3" />
        </g>
        <circle cx="32" cy="32" r="14" fill={sun} />
    {:else if condition === 'partly-cloudy'}
        <g stroke={sun} stroke-width="3.6" stroke-linecap="round">
            <line x1="40" y1="5" x2="40" y2="10" />
            <line x1="52.5" y1="9.5" x2="48.8" y2="13.2" />
            <line x1="57" y1="22" x2="52" y2="22" />
            <line x1="27.5" y1="9.5" x2="31.2" y2="13.2" />
            <line x1="23" y1="22" x2="28" y2="22" />
        </g>
        <circle cx="40" cy="22" r="10.5" fill={sun} />
        <g fill={cloud}>
            <circle cx="18" cy="39" r="10" />
            <circle cx="30" cy="33" r="12.5" />
            <circle cx="41" cy="39" r="9.5" />
            <rect x="15" y="36" width="29" height="12" rx="6" />
        </g>
    {:else if condition === 'cloudy'}
        <g fill={cloud}>
            <circle cx="21" cy="33" r="11" />
            <circle cx="33" cy="26" r="14" />
            <circle cx="45" cy="33" r="11" />
            <rect x="18" y="30" width="30" height="14" rx="7" />
        </g>
    {:else if condition === 'fog'}
        <g fill={cloud}>
            <circle cx="21" cy="26" r="11" />
            <circle cx="33" cy="19" r="14" />
            <circle cx="45" cy="26" r="11" />
            <rect x="18" y="23" width="30" height="14" rx="7" />
        </g>
        <g stroke="#B4C2D6" stroke-width="4.5" stroke-linecap="round">
            <line x1="18" y1="47" x2="46" y2="47" opacity="0.95" />
            <line x1="22" y1="54" x2="42" y2="54" opacity="0.7" />
            <line x1="26" y1="61" x2="38" y2="61" opacity="0.45" />
        </g>
    {:else if condition === 'rain'}
        <g fill={cloud}>
            <circle cx="21" cy="29" r="11" />
            <circle cx="33" cy="22" r="14" />
            <circle cx="45" cy="29" r="11" />
            <rect x="18" y="26" width="30" height="14" rx="7" />
        </g>
        <g stroke={drop} stroke-width="4.5" stroke-linecap="round">
            <line x1="22" y1="48" x2="20" y2="55" />
            <line x1="32" y1="48" x2="30" y2="55" />
            <line x1="42" y1="48" x2="40" y2="55" />
        </g>
    {:else if condition === 'thunder'}
        <g fill={cloud}>
            <circle cx="21" cy="28" r="11" />
            <circle cx="33" cy="21" r="14" />
            <circle cx="45" cy="28" r="11" />
            <rect x="18" y="25" width="30" height="14" rx="7" />
        </g>
        <path fill={bolt} d="M34 39 L23 53 L30 53 L27 63 L41 48 L34 48 L38 39 Z" />
    {:else if condition === 'dust'}
        <g stroke={dust} stroke-width="3.6" stroke-linecap="round">
            <line x1="32" y1="5" x2="32" y2="11" />
            <line x1="44" y1="13" x2="39.8" y2="17.2" />
            <line x1="52" y1="25" x2="46" y2="25" />
            <line x1="20" y1="13" x2="24.2" y2="17.2" />
            <line x1="12" y1="25" x2="18" y2="25" />
        </g>
        <circle cx="32" cy="27" r="12" fill={dust} />
        <g stroke={dust} stroke-width="4" stroke-linecap="round" fill="none">
            <path d="M14 46 C 20 42, 26 50, 32 46 C 38 42, 44 50, 50 46" />
            <path
                d="M18 55 C 24 51, 30 59, 36 55 C 40 52, 44 56, 48 54"
                opacity="0.75"
            />
        </g>
    {:else if condition === 'hot'}
        <g stroke={hot} stroke-width="3.6" stroke-linecap="round">
            <line x1="32" y1="5" x2="32" y2="11" />
            <line x1="44" y1="13" x2="39.8" y2="17.2" />
            <line x1="52" y1="25" x2="46" y2="25" />
            <line x1="20" y1="13" x2="24.2" y2="17.2" />
            <line x1="12" y1="25" x2="18" y2="25" />
        </g>
        <circle cx="32" cy="27" r="12" fill={hot} />
        <g stroke={hot} stroke-width="4" stroke-linecap="round" fill="none">
            <path d="M16 48 C 22 44, 26 52, 32 48 C 38 44, 42 52, 48 48" />
            <path
                d="M20 57 C 26 53, 30 61, 36 57 C 40 54, 43 58, 46 56"
                opacity="0.8"
            />
        </g>
    {/if}
</svg>
