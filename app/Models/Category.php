<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $type
 * @property string $color
 * @property string|null $icon
 */
#[Fillable(['name', 'type', 'color', 'icon'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    public const TYPE_EXPENSE = 'expense';

    public const TYPE_INCOME = 'income';

    protected static function booted(): void
    {
        static::updating(function (Category $category): void {
            if (! $category->isDirty('type') || ! $category->transactions()->exists()) {
                return;
            }

            throw ValidationException::withMessages([
                'type' => __('لا يمكن تغيير نوع الفئة لوجود معاملات مرتبطة بها.'),
            ]);
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
