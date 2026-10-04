const STORAGE_KEY = 'trip-draft-places';

const state = $state<{ ids: number[] }>({ ids: [] });

export function tripDraftIds(): number[] {
    return state.ids;
}

export function tripDraftCount(): number {
    return state.ids.length;
}

export function isInTripDraft(placeId: number): boolean {
    return state.ids.includes(placeId);
}

export function toggleTripDraft(placeId: number): boolean {
    const exists = state.ids.includes(placeId);

    state.ids = exists
        ? state.ids.filter((id) => id !== placeId)
        : [...state.ids, placeId];

    persist();

    return !exists;
}

export function clearTripDraft(): void {
    state.ids = [];
    persist();
}

function persist(): void {
    if (typeof window !== 'undefined') {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(state.ids));
    }
}

export function initializeTripDraft(): void {
    if (typeof window === 'undefined') {
        return;
    }

    try {
        const parsed = JSON.parse(
            localStorage.getItem(STORAGE_KEY) ?? '[]',
        ) as unknown;

        if (Array.isArray(parsed)) {
            state.ids = parsed.filter(
                (value): value is number => typeof value === 'number',
            );
        }
    } catch {
        state.ids = [];
    }
}
