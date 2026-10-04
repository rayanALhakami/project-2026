<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\InteractsWithPlaces;
use App\Models\Place;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class RecommendPlaces implements Tool
{
    use InteractsWithPlaces;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Recommend Saudi places that match the traveler interests (e.g. ["history","coffee","family"]) and budget in SAR. Returns real places with a short Arabic reason for each recommendation.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $interests = $this->stringList($request->array('interests'));
        $city = trim((string) $request->string('city'));
        $limit = $this->clampLimit($request->integer('limit', 6), 6, 20);
        $budget = $request->isNotFilled('budget_sar') ? null : max(0.0, $request->float('budget_sar'));

        $places = Place::query()
            ->with('city')
            ->when($city !== '', function (Builder $query) use ($city): void {
                $query->whereHas('city', function (Builder $builder) use ($city): void {
                    $builder->where('name', 'like', "%{$city}%")
                        ->orWhere('name_en', 'like', "%{$city}%");
                });
            })
            ->get();

        $keywords = $this->keywordsFor($interests);

        $ranked = $places->map(fn (Place $place): array => [
            'place' => $place,
            'score' => $keywords === [] ? 0 : $this->matchScore($place, $keywords),
            'matched' => $interests === [] ? [] : $this->matchedInterests($place, $interests),
            'rating' => (float) $place->rating,
            'ticket' => $this->ticketPrice($place),
        ]);

        if ($keywords !== []) {
            $matchedOnly = $ranked->filter(fn (array $row): bool => $row['score'] > 0)->values();

            if ($matchedOnly->isNotEmpty()) {
                $ranked = $matchedOnly;
            }
        }

        if ($budget !== null) {
            $affordable = $ranked->filter(fn (array $row): bool => $row['ticket'] <= $budget)->values();

            if ($affordable->count() >= min(3, $limit)) {
                $ranked = $affordable;
            }
        }

        $recommendations = $ranked
            ->sort(function (array $a, array $b): int {
                return $b['score'] <=> $a['score']
                    ?: $b['rating'] <=> $a['rating']
                    ?: $a['ticket'] <=> $b['ticket'];
            })
            ->take($limit)
            ->map(function (array $row): array {
                $place = $row['place'];

                $reason = $row['matched'] === []
                    ? 'من أعلى الأماكن تقييماً — التقييم '.number_format($row['rating'], 1).' — '.$this->priceNote($place)
                    : 'يطابق اهتمامك بـ'.implode('، ', $row['matched']).' — التقييم '.number_format($row['rating'], 1).' — '.$this->priceNote($place);

                return $this->placePayload($place, ['reason' => $reason]);
            })
            ->values()
            ->all();

        return $this->encode($recommendations);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'city' => $schema->string()
                ->description('City name in Arabic or English, e.g. "الرياض" or "Riyadh". Optional.')
                ->nullable(),
            'interests' => $schema->array()
                ->items($schema->string())
                ->description('Traveler interests, e.g. ["history","coffee","family"]. Use an empty array for top-rated recommendations.')
                ->nullable(),
            'budget_sar' => $schema->number()
                ->min(0)
                ->description('Approximate ticket budget per person in SAR. Optional.')
                ->nullable(),
            'limit' => $schema->integer()
                ->min(1)
                ->max(20)
                ->default(6)
                ->description('Maximum number of recommendations.')
                ->nullable(),
        ];
    }
}
