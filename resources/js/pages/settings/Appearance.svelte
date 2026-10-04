<script module lang="ts">
    import { edit as editAppearance } from '@/routes/appearance';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: editAppearance(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppearanceTabs from '@/components/AppearanceTabs.svelte';
    import AppHead from '@/components/AppHead.svelte';
</script>

<AppHead title="Appearance settings" />

<h1 class="sr-only">Appearance settings</h1>

<div
    class="rounded-[20px] bg-card p-6 shadow-sm ring-1 ring-border sm:p-8"
>
    <h2 class="text-lg font-bold text-foreground">Appearance settings</h2>
    <p class="mt-1 text-sm text-muted-foreground">
        Update the appearance settings for your account
    </p>
    <div class="mt-6">
        <AppearanceTabs />
    </div>
</div>
