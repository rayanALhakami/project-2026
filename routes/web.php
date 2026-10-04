<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\ContactRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlaceReviewController;
use App\Http\Controllers\PrayerTimesController;
use App\Http\Controllers\SharedTripController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\VoiceController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');
Route::inertia('welcome-classic', 'WelcomeClassic')->name('welcome-classic');

Route::inertia('places', 'Places')->name('places');
Route::inertia('translate', 'Translate')->name('translate');
Route::inertia('assistant', 'Assistant')->name('assistant');

Route::post('assistant/chat', [AssistantController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('assistant.chat');

Route::post('translate', [TranslationController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('translate.store');

Route::post('assistant/transcribe', [VoiceController::class, 'transcribe'])
    ->middleware('throttle:30,1')
    ->name('assistant.transcribe');

Route::post('assistant/speak', [VoiceController::class, 'speak'])
    ->middleware('throttle:30,1')
    ->name('assistant.speak');

Route::get('weather', [WeatherController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('weather.index');

Route::get('weather/{city}', [WeatherController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('weather.show');

Route::get('prayer-times/{city}', [PrayerTimesController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('prayer-times.show');

Route::get('shared/{token}', [SharedTripController::class, 'show'])->name('shared.show');

Route::post('plan-request', [ContactRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('contact.store');

Route::get('places/{place}/reviews', [PlaceReviewController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('places.reviews.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('trips', [TripController::class, 'index'])->name('trips');
    Route::post('trips', [TripController::class, 'store'])->name('trips.store');
    Route::post('trips/{trip}/share', [TripController::class, 'share'])->name('trips.share');
    Route::post('trips/{trip}/unshare', [TripController::class, 'unshare'])->name('trips.unshare');
    Route::get('trips/{trip}/print', [TripController::class, 'printPlan'])->name('trips.print');
    Route::patch('trip-items/{item}', [TripController::class, 'toggleItem'])->name('trip-items.toggle');
});

Route::middleware('auth')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::inertia('favorites', 'Favorites')->name('favorites');

    Route::get('favorites/ids', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('favorites/sync', [FavoriteController::class, 'sync'])->name('favorites.sync');
    Route::post('favorites/{place}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    Route::post('places/{place}/reviews', [PlaceReviewController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('places.reviews.store');

    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('analytics/data', [AnalyticsController::class, 'data'])->name('analytics.data');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
