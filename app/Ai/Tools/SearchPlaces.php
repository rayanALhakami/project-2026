<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\InteractsWithPlaces;
use App\Enums\PlaceCategory;
use App\Models\Place;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchPlaces implements Tool
{
    use InteractsWithPlaces;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search the Saudi tourist places catalogue by free-text query, city, and category. Returns real place data: Arabic and English names, city, category, ticket price in SAR, rating, opening hours, best visiting time, and coordinates.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = Place::query()->with('city');

        $search = trim((string) $request->string('query'));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('description_en', 'like', "%{$search}%")
                    ->orWhereJsonContains('tags', $search);
            });
        }

        $city = trim((string) $request->string('city'));

        if ($city !== '') {
            $query->whereHas('city', function (Builder $builder) use ($city): void {
                $builder->where('name', 'like', "%{$city}%")
                    ->orWhere('name_en', 'like', "%{$city}%");
            });
        }

        $category = $request->enum('category', PlaceCategory::class);

        if ($category instanceof PlaceCategory) {
            $query->where('category', $category);
        }

        $limit = $this->clampLimit($request->integer('limit', 8), 8, 20);

        $places = $query->orderByDesc('rating')->limit($limit)->get();

        if ($search !== '') {
            $needle = $this->normalizeSearchText($search);

            $fallback = Place::query()->with('city');

            if ($category instanceof PlaceCategory) {
                $fallback->where('category', $category);
            }

            if ($city !== '') {
                $fallback->whereHas('city', function (Builder $builder) use ($city): void {
                    $builder->where('name', 'like', "%{$city}%")
                        ->orWhere('name_en', 'like', "%{$city}%");
                });
            }

            $normalizedMatches = $fallback->get()
                ->filter(fn (Place $place): bool => str_contains($this->normalizedPlaceText($place), $needle));

            $places = $places
                ->concat($normalizedMatches)
                ->unique('id')
                ->sortByDesc(fn (Place $place): float => (float) $place->rating)
                ->take($limit)
                ->values();
        }

        return $this->encode(
            $places->map(fn (Place $place): array => $this->placePayload($place))->all(),
        );
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Free-text search across Arabic and English names, descriptions, and tags, e.g. "الحجر" or "museum". Optional.')
                ->nullable(),
            'city' => $schema->string()
                ->description('City name in Arabic or English, e.g. "العلا" or "AlUla". Optional.')
                ->nullable(),
            'category' => $schema->string()
                ->enum(PlaceCategory::class)
                ->description('Place category filter. Optional.')
                ->nullable(),
            'limit' => $schema->integer()
                ->min(1)
                ->max(20)
                ->default(8)
                ->description('Maximum number of results.')
                ->nullable(),
        ];
    }
}
