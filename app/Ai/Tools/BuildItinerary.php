<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\InteractsWithPlaces;
use App\Enums\PlaceCategory;
use App\Models\City;
use App\Models\Place;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Collection;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class BuildItinerary implements Tool
{
    use InteractsWithPlaces;

    /**
     * The start times used for the daily activity slots.
     *
     * @var array<int, string>
     */
    private const SLOT_TIMES = ['09:00', '11:30', '15:00', '18:30'];

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Build a day-by-day Saudi itinerary for a city: picks top-rated and interest-matched places, orders them by proximity (nearest-neighbor), assigns morning/afternoon/evening slots, adds a restaurant suggestion per day, and estimates the budget in SAR.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $cityName = trim((string) $request->string('city'));

        if ($cityName === '') {
            return 'يرجى تحديد المدينة المطلوبة لبناء الرحلات، مثل: الرياض، جدة، أو العلا.';
        }

        $city = City::query()
            ->where('name', 'like', "%{$cityName}%")
            ->orWhere('name_en', 'like', "%{$cityName}%")
            ->first();

        if (! $city instanceof City) {
            return 'عذراً، لم أجد مدينة باسم «'.$cityName.'» في دليلنا. جرّب مدينة أخرى مثل الرياض أو جدة أو العلا أو أبها.';
        }

        $days = max(1, min(7, $request->integer('days', 1)));
        $travelers = max(1, min(20, $request->integer('travelers', 1)));
        $interests = $this->stringList($request->array('interests'));
        $keywords = $this->keywordsFor($interests);
        $budget = $request->isNotFilled('budget_sar') ? null : max(0.0, $request->float('budget_sar'));

        $allPlaces = $city->places()->get();
        $allPlaces->each(fn (Place $place) => $place->setRelation('city', $city));

        $restaurants = $allPlaces
            ->filter(fn (Place $place): bool => $place->category === PlaceCategory::Restaurant)
            ->sortByDesc(fn (Place $place): float => (float) $place->rating)
            ->values();

        $ranked = $allPlaces
            ->reject(fn (Place $place): bool => $place->category === PlaceCategory::Restaurant)
            ->map(fn (Place $place): array => [
                'place' => $place,
                'score' => $keywords === [] ? 0 : $this->matchScore($place, $keywords),
                'rating' => (float) $place->rating,
            ])
            ->sort(function (array $a, array $b): int {
                return $b['score'] <=> $a['score']
                    ?: $b['rating'] <=> $a['rating'];
            })
            ->values();

        $selected = $ranked
            ->take($days * count(self::SLOT_TIMES))
            ->pluck('place')
            ->values();

        /** @var array<int, array<int, Place>> $dayPlaces */
        $dayPlaces = array_fill(0, $days, []);

        /** @var array<int, bool> $repeatDays */
        $repeatDays = [];

        foreach ($selected as $index => $place) {
            $dayPlaces[$index % $days][] = $place;
        }

        if ($ranked->isNotEmpty()) {
            for ($dayIndex = 0; $dayIndex < $days; $dayIndex++) {
                if ($dayPlaces[$dayIndex] !== []) {
                    continue;
                }

                $dayPlaces[$dayIndex][] = $ranked[$dayIndex % $ranked->count()]['place'];
                $repeatDays[$dayIndex] = true;
            }
        }

        $planDays = [];

        for ($day = 1; $day <= $days; $day++) {
            /** @var Collection<int, Place> $activities */
            $activities = $this->nearestNeighbourOrder(
                collect($dayPlaces[$day - 1]),
                (float) $city->latitude,
                (float) $city->longitude,
            );

            $isRepeatDay = isset($repeatDays[$day - 1]);

            $items = $activities->values()->map(fn (Place $place, int $index): array => [
                'time' => self::SLOT_TIMES[$index] ?? self::SLOT_TIMES[0],
                'place_id' => $place->id,
                'name' => $place->name,
                'category' => $place->category->value,
                'duration_minutes' => $place->avg_visit_duration ?: 90,
                'reason' => $isRepeatDay
                    ? $this->revisitReason($place, $city)
                    : $this->visitReason($place, $interests, $city),
            ])->all();

            if ($restaurants->isNotEmpty()) {
                /** @var Place $restaurant */
                $restaurant = $restaurants[($day - 1) % $restaurants->count()];

                $items[] = [
                    'time' => '13:00',
                    'place_id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'category' => $restaurant->category->value,
                    'duration_minutes' => $restaurant->avg_visit_duration ?: 60,
                    'reason' => 'اقتراح وجبة: مطعم من أعلى المطاعم تقييماً في '.$city->name.' — '.$this->priceNote($restaurant),
                ];
            }

            usort($items, fn (array $a, array $b): int => strcmp($a['time'], $b['time']));

            $planDays[] = ['day' => $day, 'items' => $items];
        }

        $scheduledPlaceIds = collect($planDays)
            ->flatMap(fn (array $day): array => $day['items'])
            ->pluck('place_id')
            ->filter()
            ->unique();

        $ticketTotal = round(
            $allPlaces
                ->whereIn('id', $scheduledPlaceIds)
                ->sum(fn (Place $place): float => $this->ticketPrice($place)),
            2,
        );

        $rooms = (int) max(1, ceil($travelers / 2));
        $meals = round(120 * $days * $travelers, 2);
        $transport = round(150 * $days, 2);
        $hotel = round(400 * $days * $rooms, 2);
        $total = round($ticketTotal + $meals + $transport + $hotel, 2);

        $budgetNote = $budget === null
            ? 'لم تُحدد ميزانية؛ التقدير مبني على نمط متوسط.'
            : ($total <= $budget
                ? 'الميزانية المحددة '.number_format($budget).' ر.س والتقدير ضمنها.'
                : 'تنبيه: التقدير يتجاوز الميزانية المحددة '.number_format($budget).' ر.س.');

        return $this->encode([
            'city' => $city->name,
            'city_en' => $city->name_en,
            'days' => $planDays,
            'estimated_ticket_total' => $ticketTotal,
            'estimated_budget' => [
                'tickets' => $ticketTotal,
                'meals' => $meals,
                'transport' => $transport,
                'hotel' => $hotel,
                'total' => $total,
                'per_person_total' => round($total / $travelers, 2),
                'travelers' => $travelers,
                'currency' => 'SAR',
                'within_budget' => $budget === null ? null : $total <= $budget,
                'notes' => $budgetNote,
            ],
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'city' => $schema->string()
                ->description('City name in Arabic or English, e.g. "العلا" or "AlUla".')
                ->required(),
            'days' => $schema->integer()
                ->min(1)
                ->max(7)
                ->description('Number of itinerary days (1 to 7).')
                ->required(),
            'interests' => $schema->array()
                ->items($schema->string())
                ->description('Optional traveler interests, e.g. ["history","coffee","family"].')
                ->nullable(),
            'budget_sar' => $schema->number()
                ->min(0)
                ->description('Optional total trip budget in SAR to check the estimate against.')
                ->nullable(),
            'travelers' => $schema->integer()
                ->min(1)
                ->max(20)
                ->description('Number of travelers; defaults to 1.')
                ->nullable(),
        ];
    }

    /**
     * Order places with a greedy nearest-neighbor walk starting from the city center.
     *
     * @param  Collection<int, Place>  $places
     * @return Collection<int, Place>
     */
    private function nearestNeighbourOrder(Collection $places, float $latitude, float $longitude): Collection
    {
        $remaining = $places->values()->all();
        $ordered = [];

        while ($remaining !== []) {
            $bestIndex = 0;
            $bestDistance = INF;

            foreach ($remaining as $index => $place) {
                $distance = $this->distanceKm($latitude, $longitude, (float) $place->latitude, (float) $place->longitude);

                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestIndex = $index;
                }
            }

            $place = $remaining[$bestIndex];
            $ordered[] = $place;
            $latitude = (float) $place->latitude;
            $longitude = (float) $place->longitude;

            unset($remaining[$bestIndex]);
        }

        return collect($ordered);
    }

    /**
     * Get the great-circle distance between two coordinates in kilometers.
     */
    private function distanceKm(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): float
    {
        $earthRadius = 6371.0;
        $latDelta = deg2rad($toLatitude - $fromLatitude);
        $lonDelta = deg2rad($toLongitude - $fromLongitude);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($fromLatitude)) * cos(deg2rad($toLatitude)) * sin($lonDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Build the Arabic reason shown for a scheduled visit.
     *
     * @param  array<int, string>  $interests
     */
    private function visitReason(Place $place, array $interests, City $city): string
    {
        $matched = $interests === [] ? [] : $this->matchedInterests($place, $interests);

        $reason = $matched === []
            ? 'من أعلى الأماكن تقييماً في '.$city->name
            : 'يطابق اهتمامك بـ'.implode('، ', $matched);

        return $reason.' — التقييم '.number_format((float) $place->rating, 1).' — '.$this->priceNote($place);
    }

    /**
     * Build the Arabic reason shown when a place is cycled onto a later day.
     */
    private function revisitReason(Place $place, City $city): string
    {
        return 'زيارة حرة أو جولة ثانية في '.$city->name.' — التقييم '.number_format((float) $place->rating, 1).' — '.$this->priceNote($place);
    }
}
