<?php

namespace App\Models;

use Database\Factories\TripFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $city_id
 * @property array<int, int>|null $city_ids
 * @property City|null $city
 * @property string $title
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property int $travelers_count
 * @property string|null $budget
 * @property array<int, string>|null $interests
 * @property string|null $notes
 * @property string|null $share_token
 * @property bool $is_public
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'city_id',
    'city_ids',
    'title',
    'start_date',
    'end_date',
    'travelers_count',
    'budget',
    'interests',
    'notes',
    'share_token',
    'is_public',
])]
class Trip extends Model
{
    /** @use HasFactory<TripFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'city_ids' => 'array',
            'interests' => 'array',
            'budget' => 'decimal:2',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get the user who owns the trip.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the primary city of the trip.
     *
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the days that make up the trip.
     *
     * @return HasMany<TripDay, $this>
     */
    public function days(): HasMany
    {
        return $this->hasMany(TripDay::class)->orderBy('day_number');
    }
}
