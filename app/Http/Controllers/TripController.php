<?php

namespace App\Http\Controllers;

use App\Enums\TripItemType;
use App\Models\City;
use App\Models\Place;
use App\Models\Trip;
use App\Models\TripItem;
use App\Support\TripPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TripController extends Controller
{
    /**
     * Show the planner with the user's latest saved trip.
     */
    public function index(Request $request): Response
    {
        $trip = $request->user()->trips()
            ->with(['city', 'days.items.place'])
            ->latest('updated_at')
            ->first();

        return Inertia::render('TripPlanner', [
            'savedTrip' => $trip ? TripPayload::forTrip($trip) : null,
        ]);
    }

    /**
     * Persist a generated itinerary for the authenticated user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:120'],
            'city_ids' => ['required', 'array', 'min:1'],
            'city_ids.*' => ['integer', 'exists:cities,id'],
            'start_date' => ['required', 'date'],
            'days' => ['required', 'integer', 'min:1', 'max:14'],
            'travelers' => ['required', 'integer', 'min:1', 'max:30'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string'],
            'plan' => ['required', 'array'],
            'plan.*.day' => ['required', 'integer', 'min:1'],
            'plan.*.date' => ['required', 'date'],
            'plan.*.city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'plan.*.entries' => ['nullable', 'array'],
            'plan.*.entries.*.place_id' => ['nullable', 'integer', 'exists:places,id'],
            'plan.*.entries.*.time' => ['nullable', 'date_format:H:i'],
            'plan.*.entries.*.duration' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $start = Carbon::parse($validated['start_date']);
            $cities = City::whereIn('id', $validated['city_ids'])->get();

            $trip = $request->user()->trips()->create([
                'title' => ($validated['title'] ?? null) ?: $this->buildTitle($cities, $validated['days']),
                'city_id' => $validated['city_ids'][0],
                'city_ids' => $validated['city_ids'],
                'start_date' => $start,
                'end_date' => $start->copy()->addDays($validated['days'] - 1),
                'travelers_count' => $validated['travelers'],
                'budget' => $validated['budget'] ?? null,
                'interests' => $validated['interests'] ?? [],
            ]);

            $placeIds = [];

            foreach ($validated['plan'] as $dayPayload) {
                foreach ($dayPayload['entries'] ?? [] as $entry) {
                    $placeId = $entry['place_id'] ?? null;

                    if (is_numeric($placeId)) {
                        $placeIds[] = (int) $placeId;
                    }
                }
            }

            $places = Place::whereIn('id', $placeIds)->get()->keyBy('id');

            foreach ($validated['plan'] as $dayPayload) {
                $day = $trip->days()->create([
                    'city_id' => $dayPayload['city_id'] ?? null,
                    'day_number' => $dayPayload['day'],
                    'date' => $dayPayload['date'],
                ]);

                foreach ($dayPayload['entries'] ?? [] as $index => $entry) {
                    $place = isset($entry['place_id']) ? $places->get($entry['place_id']) : null;

                    $day->items()->create([
                        'place_id' => $place?->id,
                        'title' => $place->name ?? '—',
                        'type' => TripItemType::Activity,
                        'start_time' => $entry['time'] ?? null,
                        'duration_minutes' => $entry['duration'] ?? $place?->avg_visit_duration,
                        'sort_order' => $index,
                    ]);
                }
            }
        });

        return back();
    }

    /**
     * Show a print-ready view of a saved trip for exporting as PDF.
     */
    public function printPlan(Request $request, Trip $trip): Response
    {
        abort_unless($trip->user_id === $request->user()->id, 403);

        return Inertia::render('TripPrint', [
            'trip' => TripPayload::forTrip($trip),
        ]);
    }

    /**
     * Toggle the completion state of a place in the itinerary.
     */
    public function toggleItem(Request $request, TripItem $item): JsonResponse
    {
        $item->loadMissing('day.trip');

        abort_unless($item->day?->trip?->user_id === $request->user()->id, 403);

        $item->completed_at = $item->completed_at === null ? now() : null;
        $item->save();

        return response()->json([
            'completed' => $item->completed_at !== null,
        ]);
    }

    /**
     * Enable the public share link for a trip and return it.
     */
    public function share(Request $request, Trip $trip): JsonResponse
    {
        abort_unless($trip->user_id === $request->user()->id, 403);

        if ($trip->share_token === null) {
            $trip->share_token = Str::random(32);
        }

        $trip->is_public = true;
        $trip->save();

        return response()->json([
            'share_url' => route('shared.show', $trip->share_token),
            'share_token' => $trip->share_token,
            'is_public' => true,
        ]);
    }

    /**
     * Disable the public share link for a trip.
     */
    public function unshare(Request $request, Trip $trip): JsonResponse
    {
        abort_unless($trip->user_id === $request->user()->id, 403);

        $trip->is_public = false;
        $trip->save();

        return response()->json(['is_public' => false]);
    }

    /**
     * Build a readable Arabic title from the selected cities.
     *
     * @param  Collection<int, City>  $cities
     */
    private function buildTitle(Collection $cities, int $days): string
    {
        $names = $cities->pluck('name')->implode(' و');

        return "رحلة {$names} — {$days} أيام";
    }
}
