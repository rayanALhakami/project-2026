import type { City, Coordinates, Place, PlaceWithDistance } from '@/types';

const EARTH_RADIUS_KM = 6371;

function toRadians(degrees: number): number {
    return (degrees * Math.PI) / 180;
}

export function haversineKm(a: Coordinates, b: Coordinates): number {
    const dLat = toRadians(b.latitude - a.latitude);
    const dLon = toRadians(b.longitude - a.longitude);
    const lat1 = toRadians(a.latitude);
    const lat2 = toRadians(b.latitude);

    const h =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLon / 2) ** 2;

    return 2 * EARTH_RADIUS_KM * Math.asin(Math.sqrt(h));
}

export function distanceToPlace(place: Place, coords: Coordinates): number {
    return haversineKm(coords, {
        latitude: place.latitude,
        longitude: place.longitude,
    });
}

export function nearestCity(cities: City[], coords: Coordinates): City | null {
    let nearest: City | null = null;
    let nearestDistance = Number.POSITIVE_INFINITY;

    for (const city of cities) {
        const distance = haversineKm(coords, {
            latitude: city.latitude,
            longitude: city.longitude,
        });

        if (distance < nearestDistance) {
            nearest = city;
            nearestDistance = distance;
        }
    }

    return nearest;
}

export function sortPlacesByDistance(
    places: Place[],
    coords: Coordinates,
): PlaceWithDistance[] {
    return places
        .map((place) => ({ place, distanceKm: distanceToPlace(place, coords) }))
        .sort((a, b) => a.distanceKm - b.distanceKm);
}

export function openStreetMapUrl(coords: Coordinates, zoom = 15): string {
    const lat = coords.latitude.toFixed(5);
    const lon = coords.longitude.toFixed(5);

    return `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lon}#map=${zoom}/${lat}/${lon}`;
}
