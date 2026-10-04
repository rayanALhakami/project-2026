<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\InteractsWithPlaces;
use App\Models\Place;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ComparePlaces implements Tool
{
    use InteractsWithPlaces;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Compare 2 to 4 Saudi places side by side. Returns price in SAR, rating, opening hours, best time, visit duration, indoor/family/wheelchair/prayer details, Friday closure, and tags. The requested order is preserved.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $ids = collect($request->array('place_ids'))
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->take(4)
            ->values();

        if ($ids->count() < 2) {
            return 'يجب تحديد معرّفين على الأقل من 1 إلى 4 أماكن لمقارنتها.';
        }

        $places = Place::query()->with('city')->whereIn('id', $ids)->get()->keyBy('id');

        $comparison = $ids->map(function (int $id) use ($places): array {
            $place = $places->get($id);

            if (! $place instanceof Place) {
                return ['id' => $id, 'error' => 'not found'];
            }

            return [
                'id' => $place->id,
                'name' => $place->name,
                'name_en' => $place->name_en,
                'city' => $place->city?->name,
                'category' => $place->category->value,
                'ticket_price' => $place->ticket_price === null ? null : (float) $place->ticket_price,
                'rating' => (float) $place->rating,
                'opening_hours' => $place->opening_hours,
                'best_time' => $place->best_time?->value,
                'avg_visit_duration' => $place->avg_visit_duration,
                'is_indoor' => $place->is_indoor,
                'family_friendly' => $place->family_friendly,
                'wheelchair_accessible' => $place->wheelchair_accessible,
                'prayer_facilities' => $place->prayer_facilities,
                'closed_friday' => $place->closed_friday,
                'tags' => $place->tags ?? [],
            ];
        })->all();

        return $this->encode($comparison);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'place_ids' => $schema->array()
                ->items($schema->integer())
                ->min(2)
                ->max(4)
                ->description('The ids of the places to compare (2 to 4). Use SearchPlaces first to find the ids.')
                ->required(),
        ];
    }
}
