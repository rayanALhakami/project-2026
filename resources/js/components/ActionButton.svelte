<script lang="ts">
    import type { LinkComponentBaseProps } from '@inertiajs/core';
    import { Link } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import { cn, toUrl } from '@/lib/utils';

    type Variant = 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger';
    type Size = 'sm' | 'md' | 'lg';

    let {
        variant = 'primary',
        size = 'md',
        href,
        onclick,
        disabled = false,
        ariaLabel,
        class: className = '',
        children,
    }: {
        variant?: Variant;
        size?: Size;
        href?: LinkComponentBaseProps['href'];
        onclick?: (event: MouseEvent) => void;
        disabled?: boolean;
        ariaLabel?: string;
        class?: string;
        children?: Snippet;
    } = $props();

    const base =
        'inline-flex items-center justify-center gap-2 rounded-full font-semibold transition active:scale-95 disabled:pointer-events-none disabled:opacity-50';

    const sizes: Record<Size, string> = {
        sm: 'min-h-11 px-4 text-sm',
        md: 'min-h-11 px-5 text-base',
        lg: 'min-h-12 px-7 py-2 text-base sm:text-lg',
    };

    const variants: Record<Variant, string> = {
        primary: 'bg-primary text-primary-foreground',
        secondary:
            'bg-secondary text-secondary-foreground hover:bg-secondary/90',
        outline:
            'border border-primary bg-transparent text-primary hover:bg-primary/5',
        ghost: 'text-primary hover:bg-primary/5',
        danger: 'bg-destructive text-destructive-foreground',
    };

    const classes = $derived(
        cn(base, sizes[size], variants[variant], className),
    );
</script>

{#if href}
    <Link href={toUrl(href)} class={classes} aria-label={ariaLabel}>
        {@render children?.()}
    </Link>
{:else}
    <button
        type="button"
        {disabled}
        onclick={onclick}
        class={classes}
        aria-label={ariaLabel}
    >
        {@render children?.()}
    </button>
{/if}
