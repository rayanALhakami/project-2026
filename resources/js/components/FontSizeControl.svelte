<script lang="ts">
    import Minus from '@lucide/svelte/icons/minus';
    import Plus from '@lucide/svelte/icons/plus';
    import Type from '@lucide/svelte/icons/type';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import {
        canDecreaseFontSize,
        canIncreaseFontSize,
        decreaseFontSize,
        fontSizePx,
        increaseFontSize,
        resetFontSize,
    } from '@/lib/font-size.svelte';
    import { formatNumber, t } from '@/lib/i18n.svelte';

    const atDefault = $derived(fontSizePx() === 17);
</script>

<DropdownMenu>
    <DropdownMenuTrigger>
        {#snippet child({ props })}
            <button
                type="button"
                {...props}
                class="inline-flex size-11 shrink-0 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                aria-label={t('fontSize.title')}
                title={t('fontSize.title')}
            >
                <Type class="size-5" aria-hidden="true" />
            </button>
        {/snippet}
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-60 p-3">
        <p class="mb-2 text-sm font-semibold text-foreground">
            {t('fontSize.title')}
        </p>
        <div class="flex items-center justify-between gap-2">
            <button
                type="button"
                onclick={decreaseFontSize}
                disabled={!canDecreaseFontSize()}
                class="flex size-11 items-center justify-center gap-0.5 rounded-full border-2 border-border bg-card text-base font-semibold text-foreground transition hover:border-primary disabled:opacity-40"
                aria-label={t('common.decrease')}
                title={t('common.decrease')}
            >
                <span aria-hidden="true">A</span>
                <Minus class="size-4" aria-hidden="true" />
            </button>
            <span class="text-lg font-semibold text-foreground">
                {formatNumber(fontSizePx())}
            </span>
            <button
                type="button"
                onclick={increaseFontSize}
                disabled={!canIncreaseFontSize()}
                class="flex size-11 items-center justify-center gap-0.5 rounded-full border-2 border-border bg-card text-base font-semibold text-foreground transition hover:border-primary disabled:opacity-40"
                aria-label={t('common.increase')}
                title={t('common.increase')}
            >
                <span aria-hidden="true">A</span>
                <Plus class="size-4" aria-hidden="true" />
            </button>
        </div>
        <button
            type="button"
            onclick={resetFontSize}
            disabled={atDefault}
            class="mt-3 min-h-10 w-full rounded-full text-sm font-semibold text-primary transition hover:bg-primary/10 disabled:opacity-40"
        >
            {t('fontSize.reset')}
        </button>
    </DropdownMenuContent>
</DropdownMenu>
