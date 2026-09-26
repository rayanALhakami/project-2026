<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
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
     * Show the reports page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $now = now();

        $from = $now->copy()->startOfMonth()->subMonths(5);
        $to = $now->copy()->endOfMonth();

        $totals = $this->totals($user, $from, $to);
        $spending = $this->spendingByCategory($user, $from, $to);

        return Inertia::render('Reports', [
            'summary' => $totals,
            'monthlyTrend' => $this->monthlyTrend($user, $now),
            'spendingByCategory' => $spending,
            'topCategories' => $this->topCategories($spending, $totals['expense']),
        ]);
    }

    /**
     * Stream a CSV export of the monthly trend.
     */
    public function export(Request $request): StreamedResponse
    {
        $trend = $this->monthlyTrend($request->user(), now());

        return response()->streamDownload(function () use ($trend): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                throw new \RuntimeException('Unable to open the CSV output stream.');
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['month', 'income', 'expense', 'net']);

            foreach ($trend as $row) {
                fputcsv($handle, [
                    $row['label'],
                    $row['income'],
                    $row['expense'],
                    $row['income'] - $row['expense'],
                ]);
            }

            fclose($handle);
        }, 'reports-export.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{expense: float, income: float, net: float}
     */
    private function totals(User $user, CarbonInterface $from, CarbonInterface $to): array
    {
        $expense = $this->sumByType($user, $from, $to, Transaction::TYPE_EXPENSE);
        $income = $this->sumByType($user, $from, $to, Transaction::TYPE_INCOME);

        return [
            'expense' => $expense,
            'income' => $income,
            'net' => $income - $expense,
        ];
    }

    /**
     * @return array<int, array{label: string, expense: float, income: float}>
     */
    private function monthlyTrend(User $user, CarbonInterface $now): array
    {
        $trend = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->startOfMonth()->subMonths($i);

            $expense = $this->sumForMonth($user, $month, Transaction::TYPE_EXPENSE);
            $income = $this->sumForMonth($user, $month, Transaction::TYPE_INCOME);

            $trend[] = [
                'label' => self::MONTH_LABELS[$month->month - 1],
                'expense' => $expense,
                'income' => $income,
            ];
        }

        return $trend;
    }

    /**
     * @return array<int, array{label: string, value: float, color: string, icon: string}>
     */
    private function spendingByCategory(User $user, CarbonInterface $from, CarbonInterface $to): array
    {
        return $user->transactions()
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->where('type', Transaction::TYPE_EXPENSE)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
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
     * @param  array<int, array{label: string, value: float, color: string, icon: string}>  $spending
     * @return array<int, array{label: string, value: float, color: string, icon: string, percent: float}>
     */
    private function topCategories(array $spending, float $totalExpense): array
    {
        return array_map(fn (array $item) => [
            ...$item,
            'percent' => $totalExpense > 0 ? round(($item['value'] / $totalExpense) * 100, 1) : 0,
        ], $spending);
    }

    private function sumByType(User $user, CarbonInterface $from, CarbonInterface $to, string $type): float
    {
        return (float) $user->transactions()
            ->where('type', $type)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
    }

    private function sumForMonth(User $user, CarbonInterface $month, string $type): float
    {
        return (float) $user->transactions()
            ->where('type', $type)
            ->whereBetween('date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');
    }
}
