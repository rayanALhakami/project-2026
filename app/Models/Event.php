<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $city_id
 * @property City|null $city
 * @property string $name
 * @property string|null $description
 * @property Carbon $start_date
 * @property Carbon|null $end_date
 * @property string|null $image
 * @property string|null $url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['city_id', 'name', 'description', 'start_date', 'end_date', 'image', 'url'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
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
        ];
    }

    /**
     * Get the city hosting the event.
     *
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
