<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import Compass from '@lucide/svelte/icons/compass';
    import UserPlus from '@lucide/svelte/icons/user-plus';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuLabel,
        DropdownMenuSeparator,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import { t } from '@/lib/i18n.svelte';
    import { toUrl } from '@/lib/utils';
    import { places, register } from '@/routes';

    let {
        label = '',
        buttonClass = '',
        arrow = false,
    }: {
        label?: string;
        buttonClass?: string;
        arrow?: boolean;
    } = $props();
</script>

<DropdownMenu>
    <DropdownMenuTrigger>
        {#snippet child({ props })}
            <button {...props} type="button" class={buttonClass}>
                {label}
                {#if arrow}
                    <ArrowLeft class="size-4 ltr:rotate-180" />
                {/if}
            </button>
        {/snippet}
    </DropdownMenuTrigger>

    <DropdownMenuContent align="end" class="w-64">
        <DropdownMenuLabel class="text-xs font-bold text-muted-foreground">
            {t('landing.guestNote')}
        </DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuItem>
            {#snippet child({ props })}
                <Link
                    href={toUrl(places())}
                    {...props}
                    class="flex w-full items-center gap-2 font-bold"
                >
                    <Compass class="size-4 text-emerald-600 dark:text-emerald-400" />
                    {t('landing.guestTour')}
                </Link>
            {/snippet}
        </DropdownMenuItem>
        <DropdownMenuItem>
            {#snippet child({ props })}
                <Link
                    href={toUrl(register())}
                    {...props}
                    class="flex w-full items-center gap-2 font-bold"
                >
                    <UserPlus class="size-4 text-emerald-600 dark:text-emerald-400" />
                    {t('landing.createAccount')}
                </Link>
            {/snippet}
        </DropdownMenuItem>
    </DropdownMenuContent>
</DropdownMenu>
