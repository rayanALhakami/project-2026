<?php

namespace App\Models;

use App\Enums\PlaceCategory;
use App\Enums\TimeOfDay;
use Database\Factories\PlaceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $city_id
 * @property string $name
 * @property string $name_en
 * @property PlaceCategory $category
 * @property string|null $description
 * @property string|null $description_en
 * @property float $latitude
 * @property float $longitude
 * @property string|null $image
 * @property string|null $ticket_price
 * @property string $rating
 * @property string|null $opening_hours
 * @property array<int, string>|null $tags
 * @property TimeOfDay|null $best_time
 * @property int|null $avg_visit_duration
 * @property bool $is_indoor
 * @property bool $family_friendly
 * @property bool $wheelchair_accessible
 * @property bool $prayer_facilities
 * @property bool $closed_friday
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'city_id',
    'name',
    'name_en',
    'category',
    'description',
    'description_en',
    'latitude',
    'longitude',
    'image',
    'ticket_price',
    'rating',
    'opening_hours',
    'tags',
    'best_time',
    'avg_visit_duration',
    'is_indoor',
    'family_friendly',
    'wheelchair_accessible',
    'prayer_facilities',
    'closed_friday',
])]
class Place extends Model
{
    /** @use HasFactory<PlaceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => PlaceCategory::class,
            'best_time' => TimeOfDay::class,
            'latitude' => 'float',
            'longitude' => 'float',
            'tags' => 'array',
            'ticket_price' => 'decimal:2',
            'rating' => 'decimal:1',
            'is_indoor' => 'boolean',
            'family_friendly' => 'boolean',
            'wheelchair_accessible' => 'boolean',
            'prayer_facilities' => 'boolean',
            'closed_friday' => 'boolean',
        ];
    }

    /**
     * Get the city the place belongs to.
     *
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the reviews written about the place.
     *
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the favorites referencing the place.
     *
     * @return HasMany<Favorite, $this>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the trip items scheduled at the place.
     *
     * @return HasMany<TripItem, $this>
     */
    public function tripItems(): HasMany
    {
        return $this->hasMany(TripItem::class);
    }

    /**
     * Scope a query to only include places in the given category.
     *
     * @param  Builder<Place>  $query
     */
    #[Scope]
    protected function category(Builder $query, PlaceCategory $category): void
    {
        $query->where('category', $category);
    }
}
