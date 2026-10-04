<?php

namespace App\Ai\Tools;

use App\Models\Event;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListEvents implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List upcoming Saudi events and seasons (Riyadh Season, Jeddah events, AlUla festivals...) with their city, dates, description, and link. Optionally filter by city.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $city = trim((string) $request->string('city'));
        $limit = max(1, min(20, $request->integer('limit', 8)));
        $today = now()->startOfDay();

        $events = Event::query()
            ->with('city')
            ->where(function (Builder $builder) use ($today): void {
                $builder->where('end_date', '>=', $today)
                    ->orWhere('start_date', '>=', $today);
            })
            ->when($city !== '', function (Builder $builder) use ($city): void {
                $builder->whereHas('city', function (Builder $query) use ($city): void {
                    $query->where('name', 'like', "%{$city}%")
                        ->orWhere('name_en', 'like', "%{$city}%");
                });
            })
            ->orderBy('start_date')
            ->limit($limit)
            ->get();

        if ($events->isEmpty()) {
            return $city === ''
                ? 'لا توجد فعاليات قادمة مسجّلة حالياً.'
                : 'لا توجد فعاليات قادمة مسجّلة حالياً في «'.$city.'».';
        }

        $payload = $events->map(fn (Event $event): array => [
            'name' => $event->name,
            'city' => $event->city?->name,
            'start_date' => $event->start_date->toDateString(),
            'end_date' => $event->end_date?->toDateString(),
            'description' => $event->description,
            'url' => $event->url,
        ])->all();

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'city' => $schema->string()
                ->description('City name in Arabic or English to filter events. Optional.')
                ->nullable(),
            'limit' => $schema->integer()
                ->min(1)
                ->max(20)
                ->default(8)
                ->description('Maximum number of events to return.')
                ->nullable(),
        ];
    }
}
