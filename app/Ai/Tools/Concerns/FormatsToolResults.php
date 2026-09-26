<?php

namespace App\Ai\Tools\Concerns;

use App\Models\Transaction;

trait FormatsToolResults
{
    /**
     * Build the standard tool result payload.
     *
     * @param  array<string, mixed>  $data
     */
    protected function result(bool $ok, string $summary, array $data = []): string
    {
        return (string) json_encode([
            'ok' => $ok,
            'summary' => $summary,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Map a transaction to the compact payload exposed to the model.
     *
     * @return array<string, mixed>
     */
    protected function compactTransaction(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'date' => $transaction->date?->toDateString(),
            'type' => $transaction->type,
            'category' => $transaction->category?->name,
            'category_id' => $transaction->category_id,
            'amount' => (float) $transaction->amount,
            'description' => $transaction->title,
        ];
    }
}
