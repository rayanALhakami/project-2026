import { cities } from '@/lib/tourism.svelte';
import type { City } from '@/types';

const STORAGE_KEY = 'selected-city';

const state = $state<{ id: number | null }>({ id: null });

export function selectedCityId(): number {
    if (state.id !== null && cities.some((city) => city.id === state.id)) {
        return state.id;
    }

    return cities[0]?.id ?? 0;
}

export function selectedCity(): City {
    return cities.find((city) => city.id === selectedCityId()) ?? cities[0];
}

export function setSelectedCity(id: number): void {
    if (!cities.some((city) => city.id === id)) {
        return;
    }

    state.id = id;

    if (typeof window !== 'undefined') {
        localStorage.setItem(STORAGE_KEY, String(id));
    }
}

export function initializeCity(): void {
    if (typeof window === 'undefined') {
        return;
    }

    const stored = Number(localStorage.getItem(STORAGE_KEY));

    if (Number.isFinite(stored)) {
        state.id = stored;
    }
}
