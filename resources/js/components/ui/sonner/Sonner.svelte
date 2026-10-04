<script lang="ts">
    import { Toaster as Sonner, type ToasterProps as SonnerProps } from 'svelte-sonner';
    import { themeState } from '@/lib/theme.svelte';

    let { ...restProps }: SonnerProps = $props();

    const { resolvedAppearance } = themeState();
</script>

<Sonner
    theme={resolvedAppearance()}
    class="toaster group"
    style="--normal-bg: var(--color-popover); --normal-text: var(--color-popover-foreground); --normal-border: var(--color-border);"
    toastOptions={{
        classes: {
            toast: 'cn-toast',
        },
    }}
    {...restProps}
/>
