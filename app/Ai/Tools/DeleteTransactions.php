<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\FormatsToolResults;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class DeleteTransactions implements Tool
{
    use FormatsToolResults;

    public function __construct(protected User $user) {}

    public function description(): Stringable|string
    {
        return <<<'TEXT'
        Delete one or more of the current user's existing transactions by ID. Deletion is permanent.

        Pass an "ids" array with the exact transaction IDs. Maximum 50 IDs per call, applied atomically. IDs that do
        not belong to the user are ignored and reported as not_found.

        Before calling this tool with transactions described in words (for example "the coffee expense"), first call
        ListTransactions to resolve the exact IDs, then delete only those IDs. If the description matches more than one
        transaction and the intent is unclear, ask the user instead of guessing.

        There is no "delete all" capability. Always provide explicit IDs.
        TEXT;
    }

    public function handle(Request $request): Stringable|string
    {
        $input = $request->all();

        $validated = Validator::make($input, [
            'ids' => ['required', 'array', 'min:1', 'max:50'],
            'ids.*' => ['required', 'integer'],
        ])->validate();

        $ids = array_values(array_unique($validated['ids']));

        $deleted = [];
        $notFound = [];

        DB::transaction(function () use ($ids, &$deleted, &$notFound): void {
            $owned = $this->user->transactions()
                ->whereIn('id', $ids)
                ->pluck('id')
                ->all();

            $notFound = array_values(array_diff($ids, $owned));

            if ($owned !== []) {
                $this->user->transactions()->whereIn('id', $owned)->delete();

                $deleted = $owned;
            }
        });

        $summary = 'حُذفت '.count($deleted).' عملية.';

        if ($notFound !== []) {
            $summary .= ' غير موجودة أو لا تخصك: '.implode('، ', $notFound).'.';
        }

        return $this->result(true, $summary, [
            'deleted_count' => count($deleted),
            'deleted_ids' => $deleted,
            'not_found' => $notFound,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'ids' => $schema->array()->min(1)->max(50)->items($schema->integer())->required()
                ->description('Exact transaction IDs to delete. Only IDs that belong to the user will be deleted.'),
        ];
    }
}
