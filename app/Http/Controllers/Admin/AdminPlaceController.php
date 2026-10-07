<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PlaceCategory;
use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminPlaceController extends Controller
{
    /**
     * List the places available for administration.
     */
    public function index(): Response
    {
        $places = Place::query()
            ->with('city')
            ->orderBy('id')
            ->get()
            ->map(fn (Place $place): array => [
                'id' => $place->id,
                'name' => $place->name,
                'name_en' => $place->name_en,
                'city_name' => $place->city?->name,
                'category' => $place->category->value,
                'ticket_price' => $place->ticket_price,
                'rating' => $place->rating,
            ])
            ->all();

        return Inertia::render('Admin/Places', [
            'places' => $places,
        ]);
    }

    /**
     * Show the edit form for a place.
     */
    public function edit(Place $place): Response
    {
        return Inertia::render('Admin/PlaceEdit', [
            'place' => [
                'id' => $place->id,
                'city_id' => $place->city_id,
                'name' => $place->name,
                'name_en' => $place->name_en,
                'category' => $place->category->value,
                'description' => $place->description,
                'ticket_price' => $place->ticket_price,
                'booking_url' => $place->booking_url,
                'rating' => $place->rating,
                'opening_hours' => $place->opening_hours,
                'is_indoor' => $place->is_indoor,
                'family_friendly' => $place->family_friendly,
                'wheelchair_accessible' => $place->wheelchair_accessible,
                'prayer_facilities' => $place->prayer_facilities,
                'closed_friday' => $place->closed_friday,
            ],
        ]);
    }

    /**
     * Update an existing place.
     */
    public function update(Request $request, Place $place): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(PlaceCategory::class)],
            'description' => ['nullable', 'string'],
            'ticket_price' => ['nullable', 'numeric', 'min:0'],
            'booking_url' => ['nullable', 'url', 'max:2048'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
            'is_indoor' => ['required', 'boolean'],
            'family_friendly' => ['required', 'boolean'],
            'wheelchair_accessible' => ['required', 'boolean'],
            'prayer_facilities' => ['required', 'boolean'],
            'closed_friday' => ['required', 'boolean'],
        ]);

        $place->update($validated);

        return back();
    }
}
