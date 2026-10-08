<?php

namespace App\Models;

use Database\Factories\ContactRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $token
 * @property string $name
 * @property string $phone
 * @property int|null $city_id
 * @property City|null $city
 * @property Carbon|null $start_date
 * @property int|null $travelers
 * @property string|null $budget
 * @property string|null $notes
 * @property string|null $plan
 * @property Carbon|null $plan_generated_at
 * @property Carbon|null $handled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['token', 'name', 'phone', 'city_id', 'start_date', 'travelers', 'budget', 'notes', 'plan', 'plan_generated_at', 'handled_at'])]
class ContactRequest extends Model
{
    /** @use HasFactory<ContactRequestFactory> */
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
            'budget' => 'decimal:2',
            'plan_generated_at' => 'datetime',
            'handled_at' => 'datetime',
        ];
    }

    /**
     * Get the city the visitor wants to start in.
     *
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
