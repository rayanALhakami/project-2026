<?php

namespace App\Http\Controllers;

use App\Concerns\SerializesTransactions;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    use SerializesTransactions;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = $user->transactions()
            ->with('category')
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($categoryId = $request->query('category')) {
            $query->where('category_id', $categoryId);
        }

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $transactions = $query->paginate(20)->withQueryString();

        return Inertia::render('Transactions', [
            'transactions' => $transactions->through(
                fn (Transaction $transaction) => $this->serializeTransaction($transaction),
            ),
            'categories' => $user->categories()
                ->withMax('transactions as last_used_id', 'id')
                ->orderByDesc('last_used_id')
                ->orderBy('name')
                ->get(),
            'filters' => $request->only(['search', 'type', 'category']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $request->user()->transactions()->create($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('تمت إضافة المعاملة بنجاح.'),
        ]);

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($request, $transaction);

        $transaction->update($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('تم تحديث المعاملة بنجاح.'),
        ]);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($request, $transaction);

        $transaction->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('تم حذف المعاملة بنجاح.'),
        ]);

        return back();
    }

    private function authorizeTransaction(Request $request, Transaction $transaction): void
    {
        abort_unless($transaction->user_id === $request->user()->id, 404);
    }
}
