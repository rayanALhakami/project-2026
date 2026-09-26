<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\FormatsToolResults;
use App\Concerns\TransactionValidationRules;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CreateTransactions implements Tool
{
    use FormatsToolResults;
    use TransactionValidationRules;

    public function __construct(protected User $user) {}

    public function description(): Stringable|string
    {
        return <<<'TEXT'
        Create one or more transactions for the current user in a single atomic call.

        Always pass a "transactions" array, even for a single transaction. Each item must contain amount (positive),
        type ("expense" or "income"), category_id (an existing category that belongs to the user and matches the type),
        and date (exact YYYY-MM-DD). "title" is optional.

        Prefer ONE call with multiple items over several calls when the user asks to add multiple transactions.
        Maximum 50 items per call; all items are inserted together or none are.

        Never send a negative amount; use type "income" for money received. Never invent a category_id: only use IDs
        from the provided category list. If a required field is missing, ask the user instead of guessing.
        TEXT;
    }

    public function handle(Request $request): Stringable|string
    {
        $input = $request->all();
        $items = $input['transactions'] ?? null;

        if (! is_array($items) || $items === []) {
            return $this->result(false, 'لم يتم تمرير أي عمليات. أرسل مصفوفة "transactions" غير فارغة.', []);
        }

        if (count($items) > 50) {
            return $this->result(false, 'عدد العمليات في الاستدعاء الواحد يتجاوز 50. قسّم الطلب إلى استدعاءات أصغر.', []);
        }

        $rules = [
            'transactions' => ['required', 'array', 'min:1', 'max:50'],
        ];

        foreach ($this->transactionRules($this->user, strictDate: true) as $field => $fieldRules) {
            $rules["transactions.*.{$field}"] = $fieldRules;
        }

        $validated = Validator::make($input, $rules, [
            'transactions.*.date.date_format' => 'Every transaction date must be an exact YYYY-MM-DD date (for example 2026-09-26). Relative expressions such as "today" or "yesterday" are not allowed.',
            'transactions.*.category_id.exists' => 'One of the category_id values does not belong to the current user or does not exist. Use only IDs from the provided category list.',
        ])->validate();

        $created = DB::transaction(function () use ($validated): array {
            return collect($validated['transactions'])
                ->map(fn (array $row) => $this->user->transactions()->create([
                    'title' => $row['title'] ?? null,
                    'amount' => $row['amount'],
                    'type' => $row['type'],
                    'category_id' => $row['category_id'],
                    'date' => $row['date'],
                ])->id)
                ->all();
        });

        return $this->result(true, 'أُضيفت '.count($created).' عملية.', [
            'created_ids' => $created,
            'created_count' => count($created),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'transactions' => $schema->array()->min(1)->max(50)->items(
                $schema->object(fn (JsonSchema $schema) => [
                    'title' => $schema->string()->max(255)->nullable()
                        ->description('Optional short description, for example "Grocery shopping".'),
                    'amount' => $schema->number()->min(0.01)->required()
                        ->description('Positive amount in SAR. Never send a negative sign; use "type" to express expense or income.'),
                    'type' => $schema->string()->enum(['expense', 'income'])->required()
                        ->description('"expense" for money spent, "income" for money received.'),
                    'category_id' => $schema->integer()->required()
                        ->description('ID of an existing category that belongs to the user and matches the transaction type.'),
                    'date' => $schema->string()->pattern('^\d{4}-\d{2}-\d{2}$')->required()
                        ->description('Exact date in YYYY-MM-DD. Never use relative words such as today or yesterday.'),
                ]),
            )->required(),
        ];
    }
}
