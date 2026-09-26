<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\FormatsToolResults;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListTransactions implements Tool
{
    use FormatsToolResults;

    public function __construct(protected User $user) {}

    public function description(): Stringable|string
    {
        return <<<'TEXT'
        List the current user's own transactions (expenses and income), optionally filtered.

        Use this tool whenever the user asks about their spending, income, totals, or wants to find specific
        transactions. Also use it to resolve transaction IDs before calling UpdateTransactions or DeleteTransactions
        when the user refers to transactions by description (for example "the coffee expense").

        It returns a compact list of transactions plus total_count (number of all matching rows) and sum_amount
        (sum of all matching rows, regardless of the limit). If truncated is true, more rows exist than were returned.

        This tool never modifies data. Never guess amounts, totals, or IDs without calling it.
        TEXT;
    }

    public function handle(Request $request): Stringable|string
    {
        $input = $request->all();

        if (isset($input['category']) && is_string($input['category'])) {
            $input['category'] = [$input['category']];
        }

        $validated = Validator::make($input, [
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
            'type' => ['nullable', 'in:expense,income'],
            'category' => ['nullable', 'array'],
            'category.*' => ['string', 'max:255'],
            'min_amount' => ['nullable', 'numeric'],
            'max_amount' => ['nullable', 'numeric'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'in:date_desc,date_asc,amount_desc,amount_asc'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], [
            'date_from.date_format' => 'date_from must be an exact YYYY-MM-DD date (for example 2026-09-26). Relative expressions such as "today" or "yesterday" are not allowed.',
            'date_to.date_format' => 'date_to must be an exact YYYY-MM-DD date (for example 2026-09-26). Relative expressions are not allowed.',
        ])->validate();

        $query = $this->user->transactions()->with('category');

        if (! empty($validated['date_from'])) {
            $query->where('date', '>=', $validated['date_from']);
        }

        if (! empty($validated['date_to'])) {
            $query->where('date', '<=', $validated['date_to']);
        }

        if (! empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (! empty($validated['category'])) {
            $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->whereIn('name', $validated['category']));
        }

        if (isset($validated['min_amount'])) {
            $query->where('amount', '>=', $validated['min_amount']);
        }

        if (isset($validated['max_amount'])) {
            $query->where('amount', '<=', $validated['max_amount']);
        }

        if (! empty($validated['search'])) {
            $query->where('title', 'like', '%'.$validated['search'].'%');
        }

        $totalCount = (clone $query)->count();
        $sumAmount = (float) (clone $query)->sum('amount');

        match ($validated['sort'] ?? 'date_desc') {
            'date_asc' => $query->orderBy('date')->orderBy('id'),
            'amount_desc' => $query->orderByDesc('amount')->orderByDesc('id'),
            'amount_asc' => $query->orderBy('amount')->orderBy('id'),
            default => $query->orderByDesc('date')->orderByDesc('id'),
        };

        $limit = $validated['limit'] ?? 25;

        $rows = $query->limit($limit)->get()
            ->map(fn ($transaction) => $this->compactTransaction($transaction))
            ->all();

        $truncated = $totalCount > count($rows);

        $summary = sprintf(
            'عُثر على %d عملية بإجمالي %s ر.س. المعروض: %d.',
            $totalCount,
            number_format($sumAmount, 2, '.', ''),
            count($rows),
        );

        if ($truncated) {
            $summary .= ' توجد نتائج أكثر من الحد المعروض؛ ضيّق الفلاتر أو ارفع الحد.';
        }

        return $this->result(true, $summary, [
            'transactions' => $rows,
            'total_count' => $totalCount,
            'sum_amount' => $sumAmount,
            'returned_count' => count($rows),
            'truncated' => $truncated,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'date_from' => $schema->string()->nullable()
                ->description('Inclusive start date. Exact format YYYY-MM-DD. Optional.'),
            'date_to' => $schema->string()->nullable()
                ->description('Inclusive end date. Exact format YYYY-MM-DD. Optional.'),
            'type' => $schema->string()->enum(['expense', 'income'])->nullable()
                ->description('Filter by transaction type. Optional.'),
            'category' => $schema->array()->items($schema->string())->nullable()
                ->description('One or more category names to filter by. Use only names from the provided category list. Optional.'),
            'min_amount' => $schema->number()->nullable()
                ->description('Only return transactions with amount greater than or equal to this value. Optional.'),
            'max_amount' => $schema->number()->nullable()
                ->description('Only return transactions with amount less than or equal to this value. Optional.'),
            'search' => $schema->string()->nullable()
                ->description('Case-insensitive text search over the transaction description. Optional.'),
            'sort' => $schema->string()->enum(['date_desc', 'date_asc', 'amount_desc', 'amount_asc'])->nullable()
                ->description('Sort order. Defaults to date_desc.'),
            'limit' => $schema->integer()->min(1)->max(100)->nullable()
                ->description('Maximum number of rows to return. Defaults to 25, maximum 100.'),
        ];
    }
}
