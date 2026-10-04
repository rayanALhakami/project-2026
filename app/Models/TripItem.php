<?php

namespace App\Models;

use App\Enums\TripItemType;
use Carbon\CarbonImmutable;
use Database\Factories\TripItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $trip_day_id
 * @property int|null $place_id
 * @property string $title
 * @property TripItemType $type
 * @property string|null $start_time
 * @property int|null $duration_minutes
 * @property string|null $notes
 * @property int $sort_order
 * @property CarbonImmutable|null $completed_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable([
    'trip_day_id',
    'place_id',
    'title',
    'type',
    'start_time',
    'duration_minutes',
    'notes',
    'sort_order',
    'completed_at',
])]
class TripItem extends Model
{
    /** @use HasFactory<TripItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TripItemType::class,
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the day the item belongs to.
     *
     * @return BelongsTo<TripDay, $this>
     */
    public function day(): BelongsTo
    {
        return $this->belongsTo(TripDay::class, 'trip_day_id');
    }

    /**
     * Get the place scheduled for the item.
     *
     * @return BelongsTo<Place, $this>
     */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
