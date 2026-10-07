<?php

namespace App\Support;

use App\Models\Trip;
use App\Models\TripDay;
use App\Models\TripItem;

class TripPayload
{
    /**
     * Shape a trip for the owner planner and the public shared page.
     *
     * @return array<string, mixed>
     */
    public static function forTrip(Trip $trip): array
    {
        $trip->loadMissing(['city', 'days.items']);

        $items = $trip->days->flatMap->items;

        return [
            'id' => $trip->id,
            'title' => $trip->title,
            'city_id' => $trip->city_id,
            'city_ids' => $trip->city_ids ?? [],
            'city_name' => $trip->city?->name,
            'city_name_en' => $trip->city?->name_en,
            'start_date' => $trip->start_date->toDateString(),
            'end_date' => $trip->end_date->toDateString(),
            'days_count' => $trip->days->count(),
            'travelers_count' => $trip->travelers_count,
            'budget' => $trip->budget,
            'interests' => $trip->interests ?? [],
            'items_count' => $items->count(),
            'completed_count' => $items->whereNotNull('completed_at')->count(),
            'share_token' => $trip->share_token,
            'is_public' => $trip->is_public,
            'share_url' => $trip->share_token !== null
                ? route('shared.show', $trip->share_token)
                : null,
            'days' => $trip->days->map(fn (TripDay $day): array => [
                'id' => $day->id,
                'day_number' => $day->day_number,
                'date' => $day->date?->toDateString(),
                'city_id' => $day->city_id,
                'items' => $day->items->map(fn (TripItem $item): array => [
                    'id' => $item->id,
                    'place_id' => $item->place_id,
                    'title' => $item->title,
                    'type' => $item->type->value,
                    'start_time' => $item->start_time,
                    'duration_minutes' => $item->duration_minutes,
                    'notes' => $item->notes,
                    'completed' => $item->completed_at !== null,
                ])->all(),
            ])->all(),
        ];
    }
}
