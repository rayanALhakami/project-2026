import { show as prayerTimesShow } from '@/routes/prayer-times';
import type { PrayerTimes } from '@/types';

const TIME_PATTERN = /^\d{1,2}:\d{2}$/;

const state = $state<{
    byCity: Record<number, PrayerTimes>;
    loading: Record<number, boolean>;
    failed: Record<number, boolean>;
}>({
    byCity: {},
    loading: {},
    failed: {},
});

const pending = new Set<number>();

function isTime(value: unknown): value is string {
    return typeof value === 'string' && TIME_PATTERN.test(value);
}

function toPrayerTimes(value: unknown, cityId: number): PrayerTimes | null {
    if (typeof value !== 'object' || value === null) {
        return null;
    }

    const row = value as Record<string, unknown>;

    if (
        !isTime(row.fajr) ||
        !isTime(row.dhuhr) ||
        !isTime(row.asr) ||
        !isTime(row.maghrib) ||
        !isTime(row.isha)
    ) {
        return null;
    }

    return {
        cityId: typeof row.cityId === 'number' ? row.cityId : cityId,
        fajr: row.fajr,
        dhuhr: row.dhuhr,
        asr: row.asr,
        maghrib: row.maghrib,
        isha: row.isha,
    };
}

export function prayerTimesFor(cityId: number): PrayerTimes | null {
    return state.byCity[cityId] ?? null;
}

export function prayerTimesLoading(cityId: number): boolean {
    return state.loading[cityId] === true;
}

export function prayerTimesFailed(cityId: number): boolean {
    return state.failed[cityId] === true;
}

export async function loadPrayerTimes(cityId: number): Promise<void> {
    if (state.byCity[cityId] || state.loading[cityId] || pending.has(cityId)) {
        return;
    }

    pending.add(cityId);
    state.loading = { ...state.loading, [cityId]: true };
    state.failed = { ...state.failed, [cityId]: false };

    try {
        const response = await fetch(prayerTimesShow(cityId).url, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error(
                `Prayer times request failed with ${response.status}.`,
            );
        }

        const times = toPrayerTimes(await response.json(), cityId);

        if (!times) {
            throw new Error('Invalid prayer times payload.');
        }

        state.byCity = { ...state.byCity, [cityId]: times };
    } catch {
        state.failed = { ...state.failed, [cityId]: true };
    } finally {
        state.loading = { ...state.loading, [cityId]: false };
        pending.delete(cityId);
    }
}
