<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with the user's most recent saved trip.
     */
    public function index(Request $request): Response
    {
        $trip = $request->user()->trips()
            ->with(['city', 'days.items'])
            ->latest('updated_at')
            ->first();

        $items = $trip?->days->flatMap->items ?? collect();

        return Inertia::render('Dashboard', [
            'savedTrip' => $trip ? [
                'id' => $trip->id,
                'title' => $trip->title,
                'city_name' => $trip->city?->name,
                'city_name_en' => $trip->city?->name_en,
                'travelers_count' => $trip->travelers_count,
                'days_count' => $trip->days->count(),
                'start_date' => $trip->start_date->toDateString(),
                'end_date' => $trip->end_date->toDateString(),
                'budget' => $trip->budget,
                'items_count' => $items->count(),
                'completed_count' => $items->whereNotNull('completed_at')->count(),
            ] : null,
        ]);
    }
}
