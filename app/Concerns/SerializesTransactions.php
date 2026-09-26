<?php

namespace App\Concerns;

use App\Models\Transaction;

trait SerializesTransactions
{
    /**
     * @return array{
     *     id: int,
     *     title: string|null,
     *     amount: float,
     *     type: string,
     *     date: string,
     *     category: array{id: int, name: string, color: string, icon: string|null}|null,
     * }
     */
    protected function serializeTransaction(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'title' => $transaction->title,
            'amount' => (float) $transaction->amount,
            'type' => $transaction->type,
            'date' => $transaction->date->toDateString(),
            'category' => $transaction->category
                ? [
                    'id' => $transaction->category->id,
                    'name' => $transaction->category->name,
                    'color' => $transaction->category->color,
                    'icon' => $transaction->category->icon,
                ]
                : null,
        ];
    }
}
