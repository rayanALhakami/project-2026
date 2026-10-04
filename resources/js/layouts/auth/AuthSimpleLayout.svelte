<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { t } from '@/lib/i18n.svelte';
    import { home } from '@/routes';

    let {
        title = '',
        description = '',
        children,
    }: {
        title?: string;
        description?: string;
        children?: Snippet;
    } = $props();
</script>

<div
    class="flex min-h-svh flex-col items-center justify-center gap-6 bg-gradient-to-br from-[#16344f] via-[#0b2337] to-[#071523] p-6 md:p-10"
>
    <div class="w-full max-w-md">
        <div class="flex flex-col gap-6">
            <div class="flex flex-col items-center">
                <Link
                    href={home()}
                    class="flex items-center gap-2.5 text-white"
                >
                    <span
                        class="flex size-11 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-700"
                    >
                        <AppLogoIcon class="size-6 fill-current" />
                    </span>
                    <span class="text-base font-bold">{t('app.name')}</span>
                    <span class="sr-only">{title}</span>
                </Link>
            </div>

            <div
                class="rounded-[24px] bg-card p-6 shadow-2xl shadow-slate-950/30 ring-1 ring-border sm:p-8"
            >
                <div class="space-y-2 text-center">
                    <h1 class="text-xl font-bold text-foreground">{title}</h1>
                    <p class="text-center text-sm text-muted-foreground">
                        {description}
                    </p>
                </div>
                <div class="mt-6">
                    {@render children?.()}
                </div>
            </div>
        </div>
    </div>
</div>
