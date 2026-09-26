<script lang="ts">
    export type BarDatum = {
        label: string;
        value: number;
    };

    let {
        data = [],
        secondaryData = [],
        color = '#0066cc',
        secondaryColor = '#34c759',
        height = 176,
        legend = false,
        format = (value: number) => value.toLocaleString('ar-SA'),
        ariaLabel = 'رسم بياني بالأعمدة يوضح القيم خلال الفترة',
    }: {
        data: BarDatum[];
        secondaryData?: BarDatum[];
        color?: string;
        secondaryColor?: string;
        height?: number;
        legend?: boolean;
        format?: (value: number) => string;
        ariaLabel?: string;
    } = $props();

    const labelReserve = 28;
    const plotHeight = $derived(height - labelReserve);

    const hasSecondary = $derived(secondaryData.length > 0);

    const max = $derived(
        Math.max(
            ...data.map((d) => d.value),
            ...secondaryData.map((d) => d.value),
            0,
        ),
    );

    const barHeight = (value: number) =>
        max > 0 && value > 0 ? Math.max((value / max) * plotHeight, 2) : 0;
</script>

<div class="w-full" role="img" aria-label={ariaLabel}>
    {#if legend}
        <div
            class="mb-3 flex items-center justify-center gap-4 text-xs text-muted-foreground"
        >
            <span class="flex items-center gap-1.5">
                <span
                    class="size-2.5 rounded-full"
                    style="background-color: {color};"
                ></span>
                الصرف
            </span>
            <span class="flex items-center gap-1.5">
                <span
                    class="size-2.5 rounded-full"
                    style="background-color: {secondaryColor};"
                ></span>
                الدخل
            </span>
        </div>
    {/if}

    <div class="flex items-end gap-2" style="height: {height}px;">
        {#each data as item, index (item.label + '-' + index)}
            <div class="flex h-full flex-1 flex-col items-center justify-end gap-1.5">
                {#if hasSecondary}
                    <div
                        class="flex flex-col items-center text-[10px] font-medium leading-tight"
                    >
                        <span style="color: {color};">{format(item.value)}</span>
                        <span style="color: {secondaryColor};">
                            {format(secondaryData[index]?.value ?? 0)}
                        </span>
                    </div>
                    <div class="flex w-full items-end justify-center gap-1">
                        <div
                            class="flex-1 rounded-t-[6px] transition-all"
                            style="height: {barHeight(item.value)}px; background-color: {color};"
                        ></div>
                        <div
                            class="flex-1 rounded-t-[6px] transition-all"
                            style="height: {barHeight(secondaryData[index]?.value ?? 0)}px; background-color: {secondaryColor};"
                        ></div>
                    </div>
                {:else}
                    <span class="text-[11px] font-medium text-muted-foreground">
                        {format(item.value)}
                    </span>
                    <div
                        class="w-full rounded-t-[6px] transition-all"
                        style="height: {barHeight(item.value)}px; background-color: {color};"
                    ></div>
                {/if}
            </div>
        {/each}
    </div>
    <div class="mt-2 flex gap-2 border-t border-border/60 pt-2">
        {#each data as item, index (item.label + '-' + index)}
            <div class="flex-1 text-center text-xs text-muted-foreground">
                {item.label}
            </div>
        {/each}
    </div>
</div>
