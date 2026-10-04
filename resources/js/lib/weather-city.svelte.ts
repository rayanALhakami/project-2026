import { t } from '@/lib/i18n.svelte';
import { cities } from '@/lib/tourism.svelte';
import { index as weatherIndex, show as weatherShow } from '@/routes/weather';
import type { Weather, WeatherCondition } from '@/types';

const STORAGE_KEY = 'weather-city';

const conditions: WeatherCondition[] = [
    'clear',
    'partly-cloudy',
    'cloudy',
    'fog',
    'rain',
    'thunder',
    'dust',
    'hot',
];

const state = $state<{
    id: number | null;
    weatherByCity: Record<number, Weather>;
    weatherLoading: boolean;
    weatherError: string | null;
}>({
    id: null,
    weatherByCity: {},
    weatherLoading: false,
    weatherError: null,
});

const pendingCities = new Set<number>();

let allWeatherLoaded = false;

function isWeatherCondition(value: unknown): value is WeatherCondition {
    return (
        typeof value === 'string' &&
        conditions.includes(value as WeatherCondition)
    );
}

function toWeather(value: unknown): Weather | null {
    if (typeof value !== 'object' || value === null) {
        return null;
    }

    const row = value as Record<string, unknown>;

    if (
        typeof row.city !== 'string' ||
        typeof row.cityEn !== 'string' ||
        typeof row.temperature !== 'number' ||
        typeof row.condition !== 'string' ||
        !isWeatherCondition(row.condition) ||
        typeof row.conditionEn !== 'string' ||
        typeof row.conditionCode !== 'number' ||
        typeof row.icon !== 'string' ||
        typeof row.high !== 'number' ||
        typeof row.low !== 'number'
    ) {
        return null;
    }

    return {
        city: row.city,
        cityEn: row.cityEn,
        temperature: row.temperature,
        condition: row.condition,
        conditionEn: row.conditionEn,
        conditionCode: row.conditionCode,
        icon: row.icon,
        high: row.high,
        low: row.low,
    };
}

function cityIdFor(weather: Weather): number | null {
    const byEnglishName = cities.find(
        (city) => city.nameEn.toLowerCase() === weather.cityEn.toLowerCase(),
    );

    if (byEnglishName) {
        return byEnglishName.id;
    }

    return cities.find((city) => city.name === weather.city)?.id ?? null;
}

function readingsFrom(payload: unknown): unknown[] | null {
    if (Array.isArray(payload)) {
        return payload;
    }

    if (typeof payload === 'object' && payload !== null) {
        const list = (payload as { cities?: unknown }).cities;

        if (Array.isArray(list)) {
            return list;
        }
    }

    return null;
}

export function weatherCityId(): number {
    if (state.id !== null && cities.some((city) => city.id === state.id)) {
        return state.id;
    }

    return cities[0]?.id ?? 0;
}

export function selectedWeather(): Weather | null {
    return state.weatherByCity[weatherCityId()] ?? null;
}

export function weatherFor(cityId: number): Weather | null {
    return state.weatherByCity[cityId] ?? null;
}

export function weatherLoading(): boolean {
    return state.weatherLoading;
}

export function weatherError(): string | null {
    return state.weatherError;
}

export function setWeatherCity(id: number): void {
    if (!cities.some((city) => city.id === id)) {
        return;
    }

    state.id = id;

    if (typeof window !== 'undefined') {
        localStorage.setItem(STORAGE_KEY, String(id));
    }
}

export function initializeWeatherCity(): void {
    if (typeof window === 'undefined') {
        return;
    }

    const stored = Number(localStorage.getItem(STORAGE_KEY));

    if (Number.isFinite(stored)) {
        state.id = stored;
    }
}

export async function loadAllWeather(force = false): Promise<void> {
    if (typeof window === 'undefined') {
        return;
    }

    if (state.weatherLoading) {
        return;
    }

    if (allWeatherLoaded && !force) {
        return;
    }

    state.weatherLoading = true;
    state.weatherError = null;

    try {
        const response = await fetch(weatherIndex().url, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error(`Weather request failed with ${response.status}.`);
        }

        const rows = readingsFrom(await response.json());

        if (!rows) {
            throw new Error('Invalid weather payload.');
        }

        const next: Record<number, Weather> = {};

        for (const row of rows) {
            const weather = toWeather(row);

            if (!weather) {
                continue;
            }

            const cityId = cityIdFor(weather);

            if (cityId !== null) {
                next[cityId] = weather;
            }
        }

        if (Object.keys(next).length === 0) {
            throw new Error('Weather payload had no usable readings.');
        }

        state.weatherByCity = next;
        allWeatherLoaded = true;
    } catch {
        state.weatherError = t('weather.error');
    } finally {
        state.weatherLoading = false;
    }
}

export async function loadWeather(cityId: number): Promise<void> {
    if (
        state.weatherByCity[cityId] ||
        pendingCities.has(cityId) ||
        !cities.some((city) => city.id === cityId)
    ) {
        return;
    }

    pendingCities.add(cityId);

    try {
        const response = await fetch(weatherShow(cityId).url, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error(`Weather request failed with ${response.status}.`);
        }

        const weather = toWeather(await response.json());

        if (!weather) {
            throw new Error('Invalid weather payload.');
        }

        state.weatherByCity = { ...state.weatherByCity, [cityId]: weather };
    } catch {
        state.weatherError = t('weather.error');
    } finally {
        pendingCities.delete(cityId);
    }
}

export async function retryWeather(): Promise<void> {
    await loadAllWeather(true);
}
