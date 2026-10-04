<script lang="ts">
    import Check from '@lucide/svelte/icons/check';
    import ChevronDown from '@lucide/svelte/icons/chevron-down';
    import {
        Dialog,
        DialogContent,
        DialogDescription,
        DialogTitle,
        DialogTrigger,
    } from '@/components/ui/dialog';
    import {
        selectedCity,
        selectedCityId,
        setSelectedCity,
    } from '@/lib/city.svelte';
    import { cityTheme } from '@/lib/city-theme';
    import { t } from '@/lib/i18n.svelte';
    import { cityName } from '@/lib/localize';
    import { cities } from '@/lib/tourism.svelte';

    let open = $state(false);
    const current = $derived(selectedCity());

    function choose(id: number): void {
        setSelectedCity(id);
        open = false;
    }
</script>

<Dialog bind:open>
    <DialogTrigger>
        {#snippet child({ props })}
            <button
                type="button"
                {...props}
                class="flex w-full items-center justify-between gap-3 rounded-full border-2 border-border bg-card px-4 py-2.5 text-start transition hover:border-primary"
            >
                <span class="flex items-center gap-3">
                    <span
                        data-city={current.id}
                        class="city-tint flex size-11 items-center justify-center rounded-full text-xl"
                        aria-hidden="true"
                    >
                        {cityTheme(current.id).emoji}
                    </span>
                    <span class="flex flex-col">
                        <span class="text-sm font-semibold text-muted-foreground">
                            {t('city.change')}
                        </span>
                        <span class="text-lg font-semibold text-foreground">
                            {cityName(current)}
                        </span>
                    </span>
                </span>
                <ChevronDown class="size-5 shrink-0 text-muted-foreground" />
            </button>
        {/snippet}
    </DialogTrigger>

    <DialogContent class="sm:max-w-lg">
        <DialogTitle class="text-xl font-semibold">{t('city.pick')}</DialogTitle>
        <DialogDescription>{t('city.pickHint')}</DialogDescription>

        <div class="grid grid-cols-2 gap-3">
            {#each cities as city (city.id)}
                {@const theme = cityTheme(city.id)}
                {@const isCurrent = city.id === selectedCityId()}
                <button
                    type="button"
                    onclick={() => choose(city.id)}
                    aria-pressed={isCurrent}
                    class="flex items-center gap-3 rounded-2xl border-2 p-3 text-start transition {isCurrent
                        ? 'border-primary bg-primary/5'
                        : 'border-border bg-card hover:border-primary/50'}"
                >
                    <span
                        data-city={city.id}
                        class="city-tint flex size-12 shrink-0 items-center justify-center rounded-full text-2xl"
                        aria-hidden="true"
                    >
                        {theme.emoji}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span
                            class="block truncate text-base font-semibold text-foreground"
                        >
                            {cityName(city)}
                        </span>
                        <span
                            class="block truncate text-sm text-muted-foreground"
                        >
                            {city.nameEn}
                        </span>
                    </span>
                    {#if isCurrent}
                        <Check class="size-5 shrink-0 text-primary" aria-hidden="true" />
                    {/if}
                </button>
            {/each}
        </div>
    </DialogContent>
</Dialog>
