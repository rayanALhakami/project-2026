<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\FormatsToolResults;
use App\Concerns\TransactionValidationRules;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class UpdateTransactions implements Tool
{
    use FormatsToolResults;
    use TransactionValidationRules;

    public function __construct(protected User $user) {}

    public function description(): Stringable|string
    {
        return <<<'TEXT'
        Update one or more of the current user's existing transactions by ID.

        Pass an "updates" array; each item must contain id plus only the fields that should change. Unchanged fields
        keep their current values. Maximum 50 items per call, applied atomically.

        Before calling this tool with transactions described in words (for example "the coffee expense"), first call
        ListTransactions to resolve the exact IDs. If the description matches more than one transaction and the intent
        is unclear, ask the user instead of guessing. Never invent an ID and never invent a category_id.
        TEXT;
    }

    public function handle(Request $request): Stringable|string
    {
        $input = $request->all();
        $items = $input['updates'] ?? null;

        if (! is_array($items) || $items === []) {
            return $this->result(false, 'لم يتم تمرير أي تعديلات. أرسل مصفوفة "updates" غير فارغة.', []);
        }

        if (count($items) > 50) {
            return $this->result(false, 'عدد التعديلات في الاستدعاء الواحد يتجاوز 50. قسّم الطلب إلى استدعاءات أصغر.', []);
        }

        $rules = [
            'updates' => ['required', 'array', 'min:1', 'max:50'],
            'updates.*.id' => ['required', 'integer'],
        ];

        foreach ($this->transactionRules($this->user, strictDate: true) as $field => $fieldRules) {
            $rules["updates.*.{$field}"] = array_merge(['sometimes'], $fieldRules);
        }

        $validated = Validator::make($input, $rules, [
            'updates.*.date.date_format' => 'Every updated date must be an exact YYYY-MM-DD date (for example 2026-09-26). Relative expressions such as "today" are not allowed.',
            'updates.*.category_id.exists' => 'One of the category_id values does not belong to the current user or does not exist. Use only IDs from the provided category list.',
        ])->validate();

        $updated = [];
        $notFound = [];

        DB::transaction(function () use ($validated, &$updated, &$notFound): void {
            foreach ($validated['updates'] as $row) {
                $id = (int) $row['id'];
                $attributes = Arr::except($row, ['id']);

                if ($attributes === []) {
                    continue;
                }

                $transaction = $this->user->transactions()->whereKey($id)->first();

                if ($transaction === null) {
                    $notFound[] = $id;

                    continue;
                }

                $transaction->update($attributes);

                $updated[] = $id;
            }
        });

        $summary = 'حُدِّثت '.count($updated).' عملية.';

        if ($notFound !== []) {
            $summary .= ' غير موجودة أو لا تخصك: '.implode('، ', $notFound).'.';
        }

        return $this->result(true, $summary, [
            'updated' => $updated,
            'updated_count' => count($updated),
            'not_found' => $notFound,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'updates' => $schema->array()->min(1)->max(50)->items(
                $schema->object(fn (JsonSchema $schema) => [
                    'id' => $schema->integer()->required()
                        ->description('ID of an existing transaction that belongs to the user.'),
                    'title' => $schema->string()->max(255)->nullable()
                        ->description('New description. Omit to keep the current one.'),
                    'amount' => $schema->number()->min(0.01)->nullable()
                        ->description('New positive amount in SAR. Omit to keep the current one.'),
                    'type' => $schema->string()->enum(['expense', 'income'])->nullable()
                        ->description('New type. Omit to keep the current one.'),
                    'category_id' => $schema->integer()->nullable()
                        ->description('New category ID that belongs to the user and matches the transaction type. Omit to keep the current one.'),
                    'date' => $schema->string()->pattern('^\d{4}-\d{2}-\d{2}$')->nullable()
                        ->description('New exact date in YYYY-MM-DD. Omit to keep the current one.'),
                ]),
            )->required(),
        ];
    }
}
