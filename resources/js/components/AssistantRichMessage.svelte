<script lang="ts">
    import AssistantPlaceCard from '@/components/AssistantPlaceCard.svelte';
    import MiniMap from '@/components/MiniMap.svelte';
    import { renderMarkdown } from '@/lib/markdown';
    import { t } from '@/lib/i18n.svelte';
    import type { AssistantComparison } from '@/lib/assistant';
    import type { Place } from '@/types';

    let {
        text,
        places = [],
        mapPlaces = [],
        comparison = null,
    }: {
        text: string;
        places?: Place[];
        mapPlaces?: Place[];
        comparison?: AssistantComparison | null;
    } = $props();
</script>

<div class="flex flex-col">
    <div class="md-body text-base leading-relaxed">
        {@html renderMarkdown(text)}
    </div>

    {#if comparison}
        <div
            class="mt-3 overflow-x-auto rounded-xl bg-card ring-1 ring-border"
        >
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <th
                            class="border-b border-border px-3 py-2 text-start text-muted-foreground"
                        >
                            {t('assistant.compareFeature')}
                        </th>
                        {#each comparison.columns as column (column)}
                            <th
                                class="border-b border-border px-3 py-2 text-start font-bold text-foreground"
                            >
                                {column}
                            </th>
                        {/each}
                    </tr>
                </thead>
                <tbody>
                    {#each comparison.rows as row (row.label)}
                        <tr>
                            <td
                                class="border-b border-border px-3 py-2 font-bold text-muted-foreground"
                            >
                                {row.label}
                            </td>
                            {#each row.values as value, index (index)}
                                <td
                                    class="border-b border-border px-3 py-2 text-secondary-foreground"
                                >
                                    {value}
                                </td>
                            {/each}
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>
    {/if}

    {#if mapPlaces.length > 0}
        <MiniMap
            places={mapPlaces}
            label={t('assistant.mapTitle')}
            class="mt-3 h-36"
        />
    {/if}

    {#if places.length > 0}
        <div class="mt-3 flex flex-col gap-2">
            {#each places as place (place.id)}
                <AssistantPlaceCard {place} />
            {/each}
        </div>
    {/if}
</div>
