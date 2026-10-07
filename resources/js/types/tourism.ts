export type PlaceCategory =
    | 'landmark'
    | 'museum'
    | 'heritage'
    | 'restaurant'
    | 'park'
    | 'nature'
    | 'beach'
    | 'shopping';

export type TimeCode =
    | 'dawn'
    | 'morning'
    | 'noon'
    | 'afternoon'
    | 'evening'
    | 'sunset'
    | 'night';

export interface City {
    id: number;
    name: string;
    nameEn: string;
    region: string;
    latitude: number;
    longitude: number;
    description: string;
    image?: string | null;
}

export interface Place {
    id: number;
    cityId: number;
    name: string;
    nameEn: string;
    category: PlaceCategory;
    description: string;
    descriptionEn: string;
    latitude: number;
    longitude: number;
    ticketPrice: number | null;
    rating: number;
    bestTime: TimeCode;
    avgVisitDuration: number;
    isIndoor: boolean;
    familyFriendly: boolean;
    wheelchairAccessible?: boolean;
    hasPrayerFacilities?: boolean;
    closedFriday?: boolean;
    openingHours?: string | null;
    image?: string | null;
    bookingUrl?: string | null;
    tags: string[];
}

export interface PrayerTimes {
    cityId: number;
    fajr: string;
    dhuhr: string;
    asr: string;
    maghrib: string;
    isha: string;
}

export interface Coordinates {
    latitude: number;
    longitude: number;
}

export interface PlaceWithDistance {
    place: Place;
    distanceKm: number;
}

export type WeatherCondition =
    | 'clear'
    | 'partly-cloudy'
    | 'cloudy'
    | 'fog'
    | 'rain'
    | 'thunder'
    | 'dust'
    | 'hot';

export interface Weather {
    city: string;
    cityEn: string;
    temperature: number;
    condition: string;
    conditionEn: string;
    conditionCode: number;
    icon: string;
    high: number;
    low: number;
}

export interface TripSummary {
    id: number;
    title: string;
    titleEn: string;
    cityName: string;
    cityNameEn: string;
    startDate: string;
    endDate: string;
    travelersCount: number;
    budget: number;
    daysCount: number;
}
