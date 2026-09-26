<?php

namespace App\Http\Controllers;

use App\Concerns\SerializesTransactions;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use SerializesTransactions;

    /**
     * @var array<int, string>
     */
    private const MONTH_LABELS = [
        'يناير',
        'فبراير',
        'مارس',
        'أبريل',
        'مايو',
        'يونيو',
        'يوليو',
        'أغسطس',
        'سبتمبر',
        'أكتوبر',
        'نوفمبر',
        'ديسمبر',
    ];

    /**
     * Show the dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $now = now();

        return Inertia::render('Dashboard', [
            'summary' => $this->summary($user, $now),
            'monthlyTrend' => $this->monthlyTrend($user, $now),
            'spendingByCategory' => $this->spendingByCategory($user, $now),
            'categoryRanking' => $this->categoryRanking($user, $now),
            'recentTransactions' => $this->recentTransactions($user),
            'categories' => $user->categories()
                ->withMax('transactions as last_used_id', 'id')
                ->orderByDesc('last_used_id')
                ->orderBy('name')
                ->get(),
            'insight' => $this->insight($user, $now),
        ]);
    }

    /**
     * @return array{spent: float, income: float, balance: float, spent_delta: float|null}
     */
    private function summary(User $user, CarbonInterface $now): array
    {
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();

        $spent = $this->sumByType($user, $start, $end, Transaction::TYPE_EXPENSE);
        $income = $this->sumByType($user, $start, $end, Transaction::TYPE_INCOME);

        $lastStart = $now->copy()->subMonth()->startOfMonth();
        $lastEnd = $now->copy()->subMonth()->endOfMonth();
        $lastSpent = $this->sumByType($user, $lastStart, $lastEnd, Transaction::TYPE_EXPENSE);

        $spentDelta = $lastSpent > 0
            ? round((($spent - $lastSpent) / $lastSpent) * 100, 1)
            : null;

        return [
            'spent' => $spent,
            'income' => $income,
            'balance' => $income - $spent,
            'spent_delta' => $spentDelta,
        ];
    }

    /**
     * @return array<int, array{label: string, value: float}>
     */
    private function monthlyTrend(User $user, CarbonInterface $now): array
    {
        $trend = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->startOfMonth()->subMonths($i);
            $value = (float) $user->transactions()
                ->where('type', Transaction::TYPE_EXPENSE)
                ->whereBetween('date', [
                    $month->copy()->startOfMonth()->toDateString(),
                    $month->copy()->endOfMonth()->toDateString(),
                ])
                ->sum('amount');

            $trend[] = [
                'label' => self::MONTH_LABELS[$month->month - 1],
                'value' => $value,
            ];
        }

        return $trend;
    }

    /**
     * @return array<int, array{label: string, value: float, color: string, icon: string}>
     */
    private function spendingByCategory(User $user, CarbonInterface $now): array
    {
        return $user->transactions()
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->where('type', Transaction::TYPE_EXPENSE)
            ->whereBetween('date', [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
            ])
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(fn (Transaction $row) => [
                'label' => $row->category->name ?? 'أخرى',
                'value' => (float) $row->getAttribute('total'),
                'color' => $row->category->color ?? '#8e8e93',
                'icon' => $row->category->icon ?? '🏷️',
            ])
            ->sortByDesc('value')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, label: string, value: float, color: string, icon: string, percent: float}>
     */
    private function categoryRanking(User $user, CarbonInterface $now): array
    {
        $totals = $user->transactions()
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->where('type', Transaction::TYPE_EXPENSE)
            ->whereBetween('date', [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
            ])
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $totalExpense = (float) $totals->sum();

        return $user->categories()
            ->where('type', Category::TYPE_EXPENSE)
            ->get()
            ->map(function (Category $category) use ($totals, $totalExpense): array {
                $value = (float) ($totals[$category->id] ?? 0);

                return [
                    'id' => $category->id,
                    'label' => $category->name,
                    'value' => $value,
                    'color' => $category->color,
                    'icon' => $category->icon ?? '🏷️',
                    'percent' => $totalExpense > 0 ? round(($value / $totalExpense) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('value')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recentTransactions(User $user): array
    {
        return $user->transactions()
            ->with('category')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn (Transaction $transaction) => $this->serializeTransaction($transaction))
            ->all();
    }

    private function sumByType(User $user, CarbonInterface $from, CarbonInterface $to, string $type): float
    {
        return (float) $user->transactions()
            ->where('type', $type)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    private function insight(User $user, CarbonInterface $now): ?string
    {
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();

        $top = $user->transactions()
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->where('type', Transaction::TYPE_EXPENSE)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->first();

        if (! $top || ! $top->category) {
            return null;
        }

        $name = $top->category->name;
        $topTotal = (float) $top->getAttribute('total');

        $lastTotal = (float) $user->transactions()
            ->where('type', Transaction::TYPE_EXPENSE)
            ->where('category_id', $top->category_id)
            ->whereBetween('date', [
                $now->copy()->subMonth()->startOfMonth()->toDateString(),
                $now->copy()->subMonth()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');

        if ($lastTotal > 0) {
            $percent = abs((int) round((($topTotal - $lastTotal) / $lastTotal) * 100));
            $direction = $topTotal >= $lastTotal ? 'ارتفع' : 'انخفض';

            return "صرفك على {$name} {$direction} بنسبة {$percent}٪ مقارنة بالشهر الماضي.";
        }

        return "أكبر فئة صرف هذا الشهر هي {$name} بمبلغ ".number_format($topTotal).' ر.س';
    }
}
