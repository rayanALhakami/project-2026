<script lang="ts">
    export type DonutSlice = {
        label: string;
        value: number;
        color: string;
    };

    let {
        data = [],
        size = 176,
        thickness = 26,
        centerTitle = '',
        centerValue = '',
        ariaLabel = 'رسم دائري يوضح توزيع القيم حسب الفئة',
    }: {
        data: DonutSlice[];
        size?: number;
        thickness?: number;
        centerTitle?: string;
        centerValue?: string;
        ariaLabel?: string;
    } = $props();

    const radius = $derived((size - thickness) / 2);
    const circumference = $derived(2 * Math.PI * radius);

    const total = $derived(data.reduce((sum, slice) => sum + slice.value, 0));

    const segments = $derived.by(() => {
        let offset = 0;
        return data.map((slice) => {
            const fraction = total > 0 ? slice.value / total : 0;
            const segment = {
                ...slice,
                fraction,
                dasharray: `${fraction * circumference} ${circumference}`,
                dashoffset: -offset * circumference,
            };
            offset += fraction;
            return segment;
        });
    });
</script>

<div
    class="relative shrink-0"
    style="width: {size}px; height: {size}px;"
    role="img"
    aria-label={ariaLabel}
>
    <svg
        viewBox={`0 0 ${size} ${size}`}
        width={size}
        height={size}
        class="-rotate-90"
    >
        {#each segments as segment, index (segment.label + '-' + index)}
            <circle
                cx={size / 2}
                cy={size / 2}
                r={radius}
                fill="none"
                stroke={segment.color}
                stroke-width={thickness}
                stroke-dasharray={segment.dasharray}
                stroke-dashoffset={segment.dashoffset}
            />
        {/each}
    </svg>
    <div
        class="absolute inset-0 flex flex-col items-center justify-center text-center"
    >
        <span
            class="text-[22px] font-bold leading-none tracking-tight text-foreground"
        >
            {centerValue}
        </span>
        {#if centerTitle}
            <span class="mt-1.5 text-xs text-muted-foreground">
                {centerTitle}
            </span>
        {/if}
    </div>
</div>
