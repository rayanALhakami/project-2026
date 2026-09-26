<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $category_id
 * @property string|null $title
 * @property string $amount
 * @property string $type
 * @property Carbon|null $date
 */
#[Fillable(['category_id', 'title', 'amount', 'type', 'date'])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    public const TYPE_EXPENSE = 'expense';

    public const TYPE_INCOME = 'income';

    protected static function booted(): void
    {
        static::saving(function (Transaction $transaction): void {
            if ($transaction->category_id === null) {
                return;
            }

            $category = Category::find($transaction->category_id);

            if ($category === null) {
                return;
            }

            if ((int) $category->user_id !== (int) $transaction->user_id) {
                throw ValidationException::withMessages(['category_id' => __('الفئة غير صالحة.')]);
            }

            if ($category->type !== $transaction->type) {
                throw ValidationException::withMessages(['category_id' => __('نوع الفئة لا يطابق نوع المعاملة.')]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return Attribute<Carbon|null, string|null>
     */
    protected function date(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?Carbon => $value === null ? null : Carbon::parse($value),
            set: fn (mixed $value): ?string => $value === null ? null : Carbon::parse($value)->toDateString(),
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
