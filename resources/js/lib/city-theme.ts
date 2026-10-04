/**
 * Per-city decorative identity for the Saudi tourist guide.
 *
 * Colours are NOT stored here — every city's `--city` / `--city-tint`
 * tokens live in `app.css` (with explicit dark-mode values) and are
 * applied through the `data-city` attribute. This file only carries the
 * decorative motif and emoji.
 */
export type CityMotif =
    | 'skyline'
    | 'sea'
    | 'dunes'
    | 'mountains'
    | 'roses'
    | 'heritage';

export interface CityTheme {
    motif: CityMotif;
    emoji: string;
}

const themes: Record<number, CityTheme> = {
    1: { motif: 'skyline', emoji: '🏙️' }, // Riyadh
    2: { motif: 'sea', emoji: '🌊' }, // Jeddah
    3: { motif: 'dunes', emoji: '🏜️' }, // AlUla
    4: { motif: 'mountains', emoji: '⛰️' }, // Abha
    5: { motif: 'roses', emoji: '🌹' }, // Taif
    6: { motif: 'sea', emoji: '⚓' }, // Khobar
    7: { motif: 'heritage', emoji: '🕌' }, // Makkah
    8: { motif: 'heritage', emoji: '🕌' }, // Madinah
    9: { motif: 'sea', emoji: '🌴' }, // Jazan
};

const fallback: CityTheme = {
    motif: 'heritage',
    emoji: '📍',
};

export function cityTheme(cityId: number): CityTheme {
    return themes[cityId] ?? fallback;
}
