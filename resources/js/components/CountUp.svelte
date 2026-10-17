<script lang="ts">
    import { onMount } from 'svelte';
    import { formatNumber } from '@/lib/i18n.svelte';

    interface Props {
        value: number;
        /** Count-up duration in ms. */
        duration?: number;
        /** Extra delay before the count-up starts, in ms. */
        delay?: number;
    }

    let { value, duration = 1600, delay = 0 }: Props = $props();

    let element = $state<HTMLSpanElement | null>(null);

    let display = $state(0);

    onMount(() => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            display = value;

            return;
        }

        if (!element) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                if (!entries.some((entry) => entry.isIntersecting)) {
                    return;
                }

                observer.disconnect();
                countUp();
            },
            { threshold: 0.5 },
        );

        observer.observe(element);

        return () => observer.disconnect();
    });

    function countUp(): void {
        const startedAt = performance.now() + delay;

        const tick = (now: number): void => {
            const progress = Math.min(
                Math.max((now - startedAt) / duration, 0),
                1,
            );
            const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);

            display = Math.round(eased * value);

            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        };

        requestAnimationFrame(tick);
    }
</script>

<span bind:this={element}>{formatNumber(display)}</span>
