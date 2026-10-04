<?php

namespace App\Ai\Tools\Concerns;

use App\Models\Place;
use Illuminate\Support\Str;

trait InteractsWithPlaces
{
    /**
     * Interest keys mapped to the Arabic and English terms found in the catalogue.
     *
     * @return array<string, array<int, string>>
     */
    protected function interestKeywords(): array
    {
        return [
            'history' => ['تاريخ', 'تراث', 'heritage', 'history', 'يونسكو', 'unesco', 'نبطي', 'nabataean', 'قلعة', 'قصر'],
            'museum' => ['متحف', 'museum', 'معرض', 'gallery', 'قاعات'],
            'heritage' => ['تراث', 'heritage', 'أثرية', 'اثرية', 'يونسكو', 'unesco', 'نبطي', 'طينية', 'قديمة'],
            'nature' => ['طبيعة', 'nature', 'جبال', 'mountain', 'قمم', 'وادي', 'valley', 'صحراء', 'desert', 'غيوم'],
            'park' => ['حديقة', 'park', 'منتزه', 'مسارات', 'تنزه', 'picnic'],
            'beach' => ['شاطئ', 'بحر', 'beach', 'sea', 'جزر', 'island', 'شعاب', 'coral', 'مرجانية', 'سباحة'],
            'coffee' => ['قهوة', 'مقهى', 'مقاهي', 'coffee', 'cafe', 'café', 'كافيه'],
            'food' => ['مطعم', 'مطاعم', 'مأكولات', 'food', 'restaurant', 'dining', 'وجبات', 'دجاج'],
            'shopping' => ['تسوق', 'سوق', 'shopping', 'mall', 'تحف', 'هدايا', 'سجاد'],
            'family' => ['عائلي', 'عائلة', 'أطفال', 'اطفال', 'family', 'kids', 'children', 'ألعاب', 'ترفيه'],
            'photography' => ['تصوير', 'photo', 'إطلالة', 'اطلالة', 'view', 'غروب', 'شروق', 'skyline'],
            'entertainment' => ['ترفيه', 'عروض', 'فعاليات', 'entertainment', 'show', 'events', 'بوليفارد'],
            'adventure' => ['مغامرة', 'adventure', 'هايكنج', 'hiking', 'تلفريك', 'cable', 'دراجات'],
            'religion' => ['مسجد', 'mosque', 'صلاة', 'جامع', 'ديني'],
            'culture' => ['ثقافة', 'culture', 'فن', 'art', 'مهرجان', 'festival', 'فنية'],
            'landmark' => ['معلم', 'landmark', 'برج', 'tower', 'جسر', 'نافورة'],
        ];
    }

    /**
     * Arabic labels for the canonical interest keys.
     *
     * @return array<string, string>
     */
    protected function interestLabels(): array
    {
        return [
            'history' => 'التاريخ',
            'museum' => 'المتاحف',
            'heritage' => 'التراث',
            'nature' => 'الطبيعة',
            'park' => 'الحدائق والمنتزهات',
            'beach' => 'البحر والشواطئ',
            'coffee' => 'القهوة والمقاهي',
            'food' => 'المطاعم والمأكولات',
            'shopping' => 'التسوق',
            'family' => 'الأنشطة العائلية',
            'photography' => 'التصوير والإطلالات',
            'entertainment' => 'الترفيه والفعاليات',
            'adventure' => 'المغامرات',
            'religion' => 'المساجد',
            'culture' => 'الثقافة والفن',
            'landmark' => 'المعالم',
        ];
    }

    /**
     * Resolve a readable Arabic label for a user interest term.
     */
    protected function interestLabel(string $interest): string
    {
        $needle = Str::lower(trim($interest));

        if ($needle === '') {
            return $interest;
        }

        if (isset($this->interestLabels()[$needle])) {
            return $this->interestLabels()[$needle];
        }

        foreach ($this->interestKeywords() as $key => $aliases) {
            foreach ($aliases as $alias) {
                if (Str::lower($alias) === $needle) {
                    return $this->interestLabels()[$key] ?? $interest;
                }
            }
        }

        return $interest;
    }

    /**
     * Expand interest terms and free-text tokens into searchable keywords.
     *
     * @param  array<int, string>  $interests
     * @return array<int, string>
     */
    protected function keywordsFor(array $interests): array
    {
        $keywords = [];

        foreach ($interests as $interest) {
            $tokens = preg_split('/\s+/u', Str::lower(trim((string) $interest))) ?: [];

            foreach ($tokens as $token) {
                if ($token === '') {
                    continue;
                }

                $keywords[] = $token;

                foreach ($this->interestKeywords() as $aliases) {
                    foreach ($aliases as $alias) {
                        if (str_contains(Str::lower($alias), $token) || str_contains($token, Str::lower($alias))) {
                            $keywords = [...$keywords, ...$aliases];

                            break 2;
                        }
                    }
                }
            }
        }

        return array_values(array_unique(array_filter($keywords, fn (string $keyword): bool => $keyword !== '')));
    }

    /**
     * Score how strongly a place matches the given keywords.
     *
     * @param  array<int, string>  $keywords
     */
    protected function matchScore(Place $place, array $keywords): int
    {
        $tags = implode(' ', $place->tags ?? []);
        $name = Str::lower(trim($place->name.' '.$place->name_en.' '.$tags));
        $body = Str::lower(trim(($place->description ?? '').' '.($place->description_en ?? '').' '.$place->category->value));
        $score = 0;

        foreach ($keywords as $keyword) {
            $keyword = Str::lower(trim($keyword));

            if ($keyword === '') {
                continue;
            }

            if (str_contains($name, $keyword)) {
                $score += 3;
            }

            if (str_contains($body, $keyword)) {
                $score += 1;
            }
        }

        return $score;
    }

    /**
     * Get the Arabic labels of the interests a place actually matches.
     *
     * @param  array<int, string>  $interests
     * @return array<int, string>
     */
    protected function matchedInterests(Place $place, array $interests): array
    {
        $matched = [];

        foreach ($interests as $interest) {
            if ($this->matchScore($place, $this->keywordsFor([$interest])) > 0) {
                $matched[] = $this->interestLabel($interest);
            }
        }

        return array_values(array_unique($matched));
    }

    /**
     * Shape a place for tool output.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function placePayload(Place $place, array $extra = []): array
    {
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
            'latitude' => $place->latitude,
            'longitude' => $place->longitude,
            ...$extra,
        ];
    }

    /**
     * Get a place ticket price as a float, treating unknown prices as free.
     */
    protected function ticketPrice(Place $place): float
    {
        return (float) ($place->ticket_price ?? 0);
    }

    /**
     * Build an Arabic one-liner about a place ticket price.
     */
    protected function priceNote(Place $place): string
    {
        if ($place->ticket_price === null) {
            return 'سعر التذكرة غير محدد';
        }

        $price = $this->ticketPrice($place);

        return $price === 0.0
            ? 'الدخول مجاني'
            : 'التذكرة '.number_format($price, $price === floor($price) ? 0 : 2).' ر.س';
    }

    /**
     * Normalize a list of strings coming from the model.
     *
     * @return array<int, string>
     */
    protected function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map(fn (mixed $item): string => is_scalar($item) ? trim((string) $item) : '', $value),
            fn (string $item): bool => $item !== '',
        ));
    }

    /**
     * Clamp a limit argument into a sane range.
     */
    protected function clampLimit(int $value, int $default, int $max): int
    {
        if ($value < 1) {
            return $default;
        }

        return min($max, $value);
    }

    /**
     * Normalize Arabic text for diacritic- and alef-insensitive matching.
     */
    protected function normalizeSearchText(string $value): string
    {
        $value = Str::lower(trim($value));
        $value = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', '', $value) ?? $value;
        $value = str_replace(['أ', 'إ', 'آ', 'ٱ'], 'ا', $value);
        $value = str_replace(['ى'], 'ي', $value);

        return str_replace(['ة'], 'ه', $value);
    }

    /**
     * Get the normalized searchable text of a place.
     */
    protected function normalizedPlaceText(Place $place): string
    {
        return $this->normalizeSearchText(implode(' ', array_filter([
            $place->name,
            $place->name_en,
            $place->description,
            $place->description_en,
            implode(' ', $place->tags ?? []),
            $place->category->value,
        ])));
    }

    /**
     * Encode tool output as readable JSON for the model.
     *
     * @param  array<int|string, mixed>  $data
     */
    protected function encode(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    }
}
