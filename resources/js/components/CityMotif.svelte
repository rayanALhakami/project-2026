<script lang="ts">
    import type { CityMotif } from '@/lib/city-theme';

    let {
        type,
        class: className = '',
    }: {
        type: CityMotif;
        class?: string;
    } = $props();
</script>

<svg
    viewBox="0 0 400 160"
    preserveAspectRatio="none"
    class={className}
    aria-hidden="true"
    focusable="false"
>
    {#if type === 'skyline'}
        <g fill="currentColor">
            <rect x="6" y="96" width="36" height="64" rx="4" />
            <rect x="48" y="64" width="30" height="96" rx="4" />
            <rect x="84" y="104" width="42" height="56" rx="4" />
            <rect x="176" y="72" width="34" height="88" rx="4" />
            <rect x="216" y="46" width="30" height="114" rx="4" />
            <path d="M231 14c10 12 14 22 14 32h-28c0-10 4-20 14-32Z" />
            <rect x="252" y="108" width="46" height="52" rx="4" />
            <rect x="304" y="82" width="30" height="78" rx="4" />
            <rect x="340" y="112" width="54" height="48" rx="4" />
        </g>
    {:else if type === 'sea'}
        <g fill="currentColor">
            <circle cx="320" cy="42" r="24" opacity="0.55" />
            <path
                d="M0 112c30-16 60-16 90 0s60 16 90 0 60-16 90 0 60 16 90 0v48H0Z"
                opacity="0.35"
            />
            <path
                d="M0 132c30-16 60-16 90 0s60 16 90 0 60-16 90 0 60 16 90 0v28H0Z"
                opacity="0.65"
            />
        </g>
    {:else if type === 'dunes'}
        <g fill="currentColor">
            <path d="M0 160V104c60-34 120-34 180 0 40 22 70 22 100 4v52Z" opacity="0.4" />
            <path d="M0 160v-40c70-30 130-24 190 6 30 15 70 15 110-6v40Z" opacity="0.7" />
        </g>
    {:else if type === 'mountains'}
        <g fill="currentColor">
            <path d="M0 160 96 44l96 116Z" opacity="0.35" />
            <path d="M120 160 232 30l112 130Z" opacity="0.6" />
            <path d="M40 160 130 66l90 94Z" opacity="0.85" />
        </g>
    {:else if type === 'roses'}
        <g fill="currentColor">
            <circle cx="90" cy="104" r="30" opacity="0.45" />
            <circle cx="150" cy="86" r="38" opacity="0.6" />
            <circle cx="216" cy="104" r="30" opacity="0.45" />
            <circle cx="150" cy="122" r="26" opacity="0.3" />
            <rect x="146" y="120" width="8" height="40" opacity="0.5" />
        </g>
    {:else}
        <g fill="currentColor">
            <path d="M40 160V78a40 40 0 0 1 80 0v82Z" opacity="0.5" />
            <rect x="150" y="42" width="70" height="118" rx="10" opacity="0.75" />
            <path d="M170 160V110a15 15 0 0 1 30 0v50Z" opacity="0.4" />
            <path d="M252 160V88a34 34 0 0 1 68 0v72Z" opacity="0.5" />
            <path d="M312 160V104a26 26 0 0 1 52 0v56Z" opacity="0.35" />
        </g>
    {/if}
</svg>
