import type { Place } from '@/types';

const state = $state<{ place: Place | null }>({ place: null });

export function openPlaceSheet(place: Place): void {
    state.place = place;
}

export function closePlaceSheet(): void {
    state.place = null;
}

export function placeSheetState(): { place: Place | null } {
    return state;
}
