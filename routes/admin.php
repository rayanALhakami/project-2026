<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminPlaceController;
use App\Http\Controllers\Admin\AdminReviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::get('places', [AdminPlaceController::class, 'index'])->name('places.index');
        Route::get('places/{place}/edit', [AdminPlaceController::class, 'edit'])->name('places.edit');
        Route::put('places/{place}', [AdminPlaceController::class, 'update'])->name('places.update');

        Route::get('events', [AdminEventController::class, 'index'])->name('events.index');
        Route::post('events', [AdminEventController::class, 'store'])->name('events.store');
        Route::put('events/{event}', [AdminEventController::class, 'update'])->name('events.update');
        Route::delete('events/{event}', [AdminEventController::class, 'destroy'])->name('events.destroy');

        Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
    });
