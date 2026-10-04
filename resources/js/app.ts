import { createInertiaApp } from '@inertiajs/svelte';
import AppLayout from '@/layouts/AppLayout.svelte';
import AuthLayout from '@/layouts/AuthLayout.svelte';
import SettingsLayout from '@/layouts/settings/Layout.svelte';
import { initializeCity } from '@/lib/city.svelte';
import { initializeFavorites } from '@/lib/favorites.svelte';
import { initializeFlashToast } from '@/lib/flash-toast';
import { initializeFontSize } from '@/lib/font-size.svelte';
import { initializeLocale, t } from '@/lib/i18n.svelte';
import { initializeTheme } from '@/lib/theme.svelte';
import { initializeVoice } from '@/lib/voice.svelte';
import { hydrateTourism } from '@/lib/tourism.svelte';
import { initializeTripDraft } from '@/lib/trip-draft.svelte';
import {
    initializeWeatherCity,
    loadAllWeather,
} from '@/lib/weather-city.svelte';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${t('app.name')}` : t('app.name')),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
            case name === 'Places':
            case name === 'Dashboard':
            case name === 'TripPlanner':
            case name === 'Translate':
            case name === 'Assistant':
            case name === 'Favorites':
            case name === 'Analytics':
            case name === 'SharedTrip':
            case name === 'TripPrint':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return SettingsLayout;
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
    withApp(context, { page }) {
        hydrateTourism((page.props as any).cities, (page.props as any).places);
        void loadAllWeather();
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will apply the saved language and direction on page load...
initializeLocale();

// Restore the visitor's voice language, gender, and auto-play preference...
initializeVoice();

// Restore the visitor's local preferences (city, favorites, trip draft)...
initializeCity();
initializeFavorites();
initializeTripDraft();
initializeWeatherCity();

// Apply the saved reading size (A- / A+) before the first paint...
initializeFontSize();

// This will listen for flash toast data from the server...
initializeFlashToast();
