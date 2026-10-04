<?php

namespace App\Models;

use Database\Factories\TripDayFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $trip_id
 * @property int|null $city_id
 * @property City|null $city
 * @property int $day_number
 * @property Carbon|null $date
 * @property string|null $title
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['trip_id', 'city_id', 'day_number', 'date', 'title', 'notes'])]
class TripDay extends Model
{
    /** @use HasFactory<TripDayFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Get the trip the day belongs to.
     *
     * @return BelongsTo<Trip, $this>
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Get the city the day is spent in.
     *
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the items scheduled during the day.
     *
     * @return HasMany<TripItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(TripItem::class)->orderBy('sort_order');
    }
}
