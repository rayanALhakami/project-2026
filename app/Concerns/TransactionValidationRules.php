<?php

namespace App\Concerns;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Validation\Rule;

trait TransactionValidationRules
{
    /**
     * Get the shared validation rules for a transaction.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function transactionRules(User $user, bool $strictDate = false): array
    {
        $date = ['required', 'date'];

        if ($strictDate) {
            $date[] = 'date_format:Y-m-d';
        }

        return [
            'title' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'type' => ['required', Rule::in([Transaction::TYPE_EXPENSE, Transaction::TYPE_INCOME])],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(
                    fn ($query) => $query->where('user_id', $user->id),
                ),
            ],
            'date' => $date,
        ];
    }
}
