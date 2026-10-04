<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\InteractsWithPlaces;
use App\Models\Place;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class FindBestFor implements Tool
{
    use InteractsWithPlaces;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Find the best Saudi places for a specific need such as "قهوة مختصة", "متحف", "أطفال", or "تصوير". Ranks real places by keyword relevance and returns a short Arabic reason for each result.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $need = trim((string) $request->string('need'));

        if ($need === '') {
            return 'يرجى تحديد الحاجة المطلوبة، مثل: قهوة مختصة، متحف، أطفال، أو تصوير.';
        }

        $city = trim((string) $request->string('city'));
        $limit = $this->clampLimit($request->integer('limit', 5), 5, 20);
        $keywords = $this->keywordsFor([$need]);

        $places = Place::query()
            ->with('city')
            ->when($city !== '', function (Builder $query) use ($city): void {
                $query->whereHas('city', function (Builder $builder) use ($city): void {
                    $builder->where('name', 'like', "%{$city}%")
                        ->orWhere('name_en', 'like', "%{$city}%");
                });
            })
            ->get();

        $ranked = $places->map(fn (Place $place): array => [
            'place' => $place,
            'score' => $this->matchScore($place, $keywords),
            'rating' => (float) $place->rating,
        ]);

        $matchedOnly = $ranked->filter(fn (array $row): bool => $row['score'] > 0)->values();

        $fallback = $matchedOnly->isEmpty();
        $ranked = $fallback ? $ranked : $matchedOnly;

        $results = $ranked
            ->sort(function (array $a, array $b): int {
                return $b['score'] <=> $a['score']
                    ?: $b['rating'] <=> $a['rating'];
            })
            ->take($limit)
            ->map(function (array $row) use ($need, $fallback): array {
                $place = $row['place'];

                $reason = $fallback
                    ? 'لم أجد مطابقة مباشرة لطلبك «'.$need.'»، وهذا من أعلى الأماكن تقييماً — التقييم '.number_format($row['rating'], 1).' — '.$this->priceNote($place)
                    : 'من أفضل النتائج لطلبك «'.$need.'» — التقييم '.number_format($row['rating'], 1).' — '.$this->priceNote($place);

                return $this->placePayload($place, ['reason' => $reason]);
            })
            ->values()
            ->all();

        return $this->encode($results);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'need' => $schema->string()
                ->description('What the traveler is looking for, e.g. "قهوة مختصة", "متحف", "أطفال", or "تصوير".')
                ->required(),
            'city' => $schema->string()
                ->description('City name in Arabic or English to narrow the search. Optional.')
                ->nullable(),
            'limit' => $schema->integer()
                ->min(1)
                ->max(20)
                ->default(5)
                ->description('Maximum number of results.')
                ->nullable(),
        ];
    }
}
