export interface RevealOptions {
    /** Delay before the reveal starts, in ms. */
    delay?: number;
    /** Vertical travel distance, in px. */
    distance?: number;
}

export interface RevealAction {
    update: (options: RevealOptions) => void;
    destroy: () => void;
}

function prefersReducedMotion(): boolean {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * Reveals an element with a soft rise once it enters the viewport.
 * The element stays fully visible for reduced-motion users — nothing is
 * hidden without an animation to bring it back.
 */
export function reveal(
    node: HTMLElement,
    options: RevealOptions = {},
): RevealAction | undefined {
    if (prefersReducedMotion()) {
        return undefined;
    }

    const applyOptions = (next: RevealOptions): void => {
        if (next.delay !== undefined) {
            node.style.setProperty('--reveal-delay', `${next.delay}ms`);
        }

        if (next.distance !== undefined) {
            node.style.setProperty('--reveal-y', `${next.distance}px`);
        }
    };

    applyOptions(options);
    node.dataset.reveal = '';

    const isAlreadyVisible = (): boolean => {
        const rect = node.getBoundingClientRect();

        return rect.top < window.innerHeight && rect.bottom > 0;
    };

    if (isAlreadyVisible()) {
        node.classList.add('is-revealed');

        return { update: applyOptions, destroy: () => {} };
    }

    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (!entry.isIntersecting) {
                    continue;
                }

                node.classList.add('is-revealed');
                observer.disconnect();
            }
        },
        { rootMargin: '0px 0px -60px 0px', threshold: 0.15 },
    );

    observer.observe(node);

    return {
        update: applyOptions,
        destroy: () => observer.disconnect(),
    };
}
