<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    /**
     * Show the authenticated user's travel statistics page.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Analytics', [
            'stats' => $this->stats($request),
        ]);
    }

    /**
     * Return the authenticated user's travel statistics as JSON.
     */
    public function data(Request $request): JsonResponse
    {
        return response()->json($this->stats($request));
    }

    /**
     * Summarize the authenticated user's travel activity.
     *
     * @return array<string, mixed>
     */
    private function stats(Request $request): array
    {
        $user = $request->user();

        $tripsCount = $user->trips()->count();
        $upcomingTripsCount = $user->trips()
            ->where('end_date', '>=', Carbon::today())
            ->count();

        $itemsQuery = TripItem::query()
            ->whereHas('day.trip', fn (Builder $query): Builder => $query->where('user_id', $user->id));

        $totalItemsCount = (clone $itemsQuery)->count();
        $completedItemsCount = (clone $itemsQuery)->whereNotNull('completed_at')->count();

        $citiesVisitedCount = $user->trips()
            ->get(['city_id', 'city_ids'])
            ->flatMap(fn (Trip $trip): array => array_values(array_filter([
                $trip->city_id,
                ...($trip->city_ids ?? []),
            ])))
            ->unique()
            ->count();

        $topInterests = $this->topInterests($user->trips()->get(['interests']));

        return [
            'trips_count' => $tripsCount,
            'upcoming_trips_count' => $upcomingTripsCount,
            'completed_items_count' => $completedItemsCount,
            'total_items_count' => $totalItemsCount,
            'cities_visited_count' => $citiesVisitedCount,
            'favorite_places_count' => $user->favorites()->count(),
            'top_interests' => $topInterests,
        ];
    }

    /**
     * Count the user's trip interests and keep the five most frequent.
     *
     * @param  Collection<int, Trip>  $trips
     * @return array<int, array{interest: string, count: int}>
     */
    private function topInterests(Collection $trips): array
    {
        return $trips
            ->flatMap(fn (Trip $trip): array => $trip->interests ?? [])
            ->countBy()
            ->sortDesc()
            ->take(5)
            ->map(fn (int $count, string|int $interest): array => [
                'interest' => (string) $interest,
                'count' => $count,
            ])
            ->values()
            ->all();
    }
}
