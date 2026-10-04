import { getLocale, t } from '@/lib/i18n.svelte';
import type { MessageKey } from '@/lib/i18n.svelte';
import type {
    City,
    Place,
    TripSummary,
    Weather,
    WeatherCondition,
} from '@/types';

function isArabic(): boolean {
    return getLocale() === 'ar';
}

export function cityName(city: City): string {
    return isArabic() ? city.name : city.nameEn;
}

export function placeName(place: Place): string {
    return isArabic() ? place.name : place.nameEn;
}

export function placeDescription(place: Place): string {
    return isArabic() ? place.description : place.descriptionEn;
}

export function tripTitle(trip: TripSummary): string {
    return isArabic() ? trip.title : trip.titleEn;
}

export function tripCity(trip: TripSummary): string {
    return isArabic() ? trip.cityName : trip.cityNameEn;
}

export function weatherCity(weather: Weather): string {
    return isArabic() ? weather.city : weather.cityEn;
}

const conditionKeys: Record<string, MessageKey | undefined> = {
    clear: 'conditions.clear',
    'partly-cloudy': 'conditions.partlyCloudy',
    cloudy: 'conditions.cloudy',
    fog: 'conditions.fog',
    rain: 'conditions.rain',
    thunder: 'conditions.thunder',
    dust: 'conditions.dust',
    hot: 'conditions.hot',
};

export function weatherCondition(weather: Weather): string {
    const key = conditionKeys[weather.condition as WeatherCondition];

    if (key) {
        return t(key);
    }

    return weather.conditionEn || weather.condition;
}
