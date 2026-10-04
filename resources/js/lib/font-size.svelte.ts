const STORAGE_KEY = 'app-font-size';

export const FONT_SIZE_STEPS = [15, 16, 17, 18, 19, 20] as const;

const DEFAULT_STEP_INDEX = FONT_SIZE_STEPS.indexOf(17);

const state = $state<{ stepIndex: number }>({ stepIndex: DEFAULT_STEP_INDEX });

function applyFontSize(): void {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.style.setProperty(
        '--app-font-size',
        `${FONT_SIZE_STEPS[state.stepIndex]}px`,
    );
}

export function fontSizePx(): number {
    return FONT_SIZE_STEPS[state.stepIndex];
}

export function canIncreaseFontSize(): boolean {
    return state.stepIndex < FONT_SIZE_STEPS.length - 1;
}

export function canDecreaseFontSize(): boolean {
    return state.stepIndex > 0;
}

export function increaseFontSize(): void {
    if (!canIncreaseFontSize()) {
        return;
    }

    state.stepIndex += 1;
    persist();
}

export function decreaseFontSize(): void {
    if (!canDecreaseFontSize()) {
        return;
    }

    state.stepIndex -= 1;
    persist();
}

export function resetFontSize(): void {
    state.stepIndex = DEFAULT_STEP_INDEX;
    persist();
}

function persist(): void {
    if (typeof window !== 'undefined') {
        localStorage.setItem(STORAGE_KEY, String(state.stepIndex));
    }

    applyFontSize();
}

export function initializeFontSize(): void {
    if (typeof window === 'undefined') {
        return;
    }

    const stored = Number(localStorage.getItem(STORAGE_KEY));

    if (
        Number.isInteger(stored) &&
        stored >= 0 &&
        stored < FONT_SIZE_STEPS.length
    ) {
        state.stepIndex = stored;
    }

    applyFontSize();
}
