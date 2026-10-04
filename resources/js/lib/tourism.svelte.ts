import { router } from '@inertiajs/svelte';
import type { City, Place, PlaceCategory, TimeCode } from '@/types';

export const categoryMeta: Record<
    PlaceCategory,
    { label: string; icon: string; color: string }
> = {
    landmark: { label: 'معلم', icon: '🏛️', color: '#0066cc' },
    museum: { label: 'متحف', icon: '🖼️', color: '#5856d6' },
    heritage: { label: 'تراث', icon: '🏰', color: '#ff9500' },
    restaurant: { label: 'مطعم', icon: '🍽️', color: '#ff3b30' },
    park: { label: 'حديقة', icon: '🌳', color: '#34c759' },
    nature: { label: 'طبيعة', icon: '🏔️', color: '#30b0c7' },
    beach: { label: 'شاطئ', icon: '🏖️', color: '#0a84ff' },
    shopping: { label: 'تسوق', icon: '🛍️', color: '#af52de' },
};

type CityRow = {
    id: number;
    name: string;
    name_en: string;
    region?: string | null;
    latitude: number | string;
    longitude: number | string;
    description?: string | null;
    image?: string | null;
};

type PlaceRow = {
    id: number;
    city_id: number;
    name: string;
    name_en: string;
    category: PlaceCategory;
    description?: string | null;
    description_en?: string | null;
    latitude: number | string;
    longitude: number | string;
    image?: string | null;
    ticket_price?: number | string | null;
    rating?: number | string | null;
    opening_hours?: string | null;
    tags?: string[] | null;
    best_time?: TimeCode | null;
    avg_visit_duration?: number | null;
    is_indoor: boolean;
    family_friendly: boolean;
    wheelchair_accessible: boolean;
    prayer_facilities: boolean;
    closed_friday: boolean;
};

function toCity(row: CityRow): City {
    return {
        id: row.id,
        name: row.name,
        nameEn: row.name_en,
        region: row.region ?? '',
        latitude: Number(row.latitude),
        longitude: Number(row.longitude),
        description: row.description ?? '',
        image: row.image ?? null,
    };
}

function toPlace(row: PlaceRow): Place {
    return {
        id: row.id,
        cityId: row.city_id,
        name: row.name,
        nameEn: row.name_en,
        category: row.category,
        description: row.description ?? '',
        descriptionEn: row.description_en ?? '',
        latitude: Number(row.latitude),
        longitude: Number(row.longitude),
        ticketPrice: row.ticket_price == null ? null : Number(row.ticket_price),
        rating: Number(row.rating ?? 0),
        bestTime: row.best_time ?? 'morning',
        avgVisitDuration: row.avg_visit_duration ?? 0,
        isIndoor: row.is_indoor,
        familyFriendly: row.family_friendly,
        wheelchairAccessible: row.wheelchair_accessible,
        hasPrayerFacilities: row.prayer_facilities,
        closedFriday: row.closed_friday,
        openingHours: row.opening_hours ?? undefined,
        image: row.image ?? null,
        tags: row.tags ?? [],
    };
}

export const cities = $state<City[]>([]);
export const places = $state<Place[]>([]);

export function hydrateTourism(
    citiesRows?: CityRow[] | null,
    placesRows?: PlaceRow[] | null,
): void {
    if (citiesRows) {
        cities.length = 0;
        cities.push(...citiesRows.map(toCity));
    }

    if (placesRows) {
        places.length = 0;
        places.push(...placesRows.map(toPlace));
    }
}

router.on('navigate', (event) => {
    const props = (event.detail?.page?.props ?? {}) as {
        cities?: CityRow[];
        places?: PlaceRow[];
    };

    hydrateTourism(props.cities, props.places);
});

export function cityById(id: number): City | undefined {
    return cities.find((city) => city.id === id);
}

export function placeById(id: number): Place | undefined {
    return places.find((place) => place.id === id);
}

export function placesByCity(cityId: number): Place[] {
    return places.filter((place) => place.cityId === cityId);
}

export function topPlaces(limit = 6): Place[] {
    return [...places].sort((a, b) => b.rating - a.rating).slice(0, limit);
}
