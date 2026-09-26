<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

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

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::create(2026, 6, 15, 12, 0, 0));
        $this->user = User::factory()->create();
    }

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get(route('reports'))->assertRedirect(route('login'));
    }

    public function test_reports_page_is_displayed_with_the_expected_props()
    {
        $response = $this->actingAs($this->user)->get(route('reports'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Reports')
            ->hasAll(['summary', 'monthlyTrend', 'spendingByCategory', 'topCategories'])
        );
    }

    public function test_summary_window_matches_the_monthly_trend_window()
    {
        // Current month.
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 100, now()->startOfMonth()->addDay());
        $this->makeTransaction($this->user, Transaction::TYPE_INCOME, 300, now()->startOfMonth()->addDay());

        // Three months ago (inside the six month window).
        $threeMonthsAgo = now()->copy()->startOfMonth()->subMonths(3)->addDay();
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 200, $threeMonthsAgo);
        $this->makeTransaction($this->user, Transaction::TYPE_INCOME, 400, $threeMonthsAgo);

        // Later in the current month (a future date): must be inside the window.
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 50, now()->copy()->addDays(5));

        // Seven months ago (outside the six month window): must be excluded.
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 999, now()->copy()->startOfMonth()->subMonths(6)->addDay());

        $response = $this->actingAs($this->user)->get(route('reports'));

        $summary = $response->inertiaProps('summary');
        $trend = $response->inertiaProps('monthlyTrend');

        $trendExpense = array_sum(array_column($trend, 'expense'));
        $trendIncome = array_sum(array_column($trend, 'income'));

        $this->assertEqualsWithDelta(350.0, $summary['expense'], 0.001);
        $this->assertEqualsWithDelta(700.0, $summary['income'], 0.001);
        $this->assertEqualsWithDelta(350.0, $summary['net'], 0.001);

        // The regression: the summary and the trend must share the exact same window.
        $this->assertEqualsWithDelta($summary['expense'], $trendExpense, 0.001);
        $this->assertEqualsWithDelta($summary['income'], $trendIncome, 0.001);
        $this->assertEqualsWithDelta(350.0, $trendExpense, 0.001);
        $this->assertEqualsWithDelta(700.0, $trendIncome, 0.001);
    }

    /**
     * Regression for a real bug: the `date` cast stores "Y-m-d 00:00:00" while the
     * controllers filter with date-only bounds, so a transaction on the last day of a
     * month is dropped by the lexicographic comparison and disappears from every total.
     */
    public function test_transactions_on_the_last_day_of_a_month_are_included_in_totals()
    {
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 30, now()->startOfMonth()->addDay());
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 50, now()->copy()->endOfMonth());
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 70, now()->copy()->startOfMonth()->subMonths(2)->endOfMonth());

        $response = $this->actingAs($this->user)->get(route('reports'));

        $summary = $response->inertiaProps('summary');
        $trend = $response->inertiaProps('monthlyTrend');

        $this->assertEqualsWithDelta(150.0, $summary['expense'], 0.001);
        $this->assertEqualsWithDelta(80.0, $trend[5]['expense'], 0.001);
        $this->assertEqualsWithDelta(70.0, $trend[3]['expense'], 0.001);
    }

    public function test_monthly_trend_has_six_months_with_expense_and_income()
    {
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 60, now()->startOfMonth()->addDay());
        $this->makeTransaction($this->user, Transaction::TYPE_INCOME, 90, now()->copy()->startOfMonth()->subMonths(2)->addDay());

        $trend = $this->actingAs($this->user)
            ->get(route('reports'))
            ->inertiaProps('monthlyTrend');

        $this->assertCount(6, $trend);
        $this->assertSame($this->expectedTrendLabels(), array_column($trend, 'label'));

        foreach ($trend as $entry) {
            $this->assertArrayHasKey('label', $entry);
            $this->assertArrayHasKey('expense', $entry);
            $this->assertArrayHasKey('income', $entry);
        }

        $this->assertEqualsWithDelta(60.0, $trend[5]['expense'], 0.001);
        $this->assertEqualsWithDelta(0.0, $trend[5]['income'], 0.001);
        $this->assertEqualsWithDelta(90.0, $trend[3]['income'], 0.001);
    }

    public function test_spending_by_category_sums_to_the_summary_and_is_sorted_descending()
    {
        $groceries = Category::factory()->expense()->create([
            'user_id' => $this->user->id,
            'name' => 'بقالة',
            'color' => '#34c759',
            'icon' => '🛒',
        ]);
        $restaurants = Category::factory()->expense()->create([
            'user_id' => $this->user->id,
            'name' => 'مطاعم',
            'color' => '#ff9500',
            'icon' => '🍽️',
        ]);

        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 100, now()->startOfMonth()->addDay(), $groceries);
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 150, now()->copy()->startOfMonth()->subMonths(2)->addDay(), $groceries);
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 300, now()->startOfMonth()->addDay(), $restaurants);
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 40, now()->copy()->startOfMonth()->subMonths(4)->addDay());

        $response = $this->actingAs($this->user)->get(route('reports'));

        $summary = $response->inertiaProps('summary');
        $spending = $response->inertiaProps('spendingByCategory');

        $this->assertEqualsWithDelta(590.0, $summary['expense'], 0.001);
        $this->assertEqualsWithDelta($summary['expense'], array_sum(array_column($spending, 'value')), 0.001);

        $values = array_column($spending, 'value');
        $sorted = $values;
        rsort($sorted);
        $this->assertSame($sorted, $values);

        $this->assertSame('مطاعم', $spending[0]['label']);
        $this->assertEqualsWithDelta(300.0, $spending[0]['value'], 0.001);
        $this->assertSame('بقالة', $spending[1]['label']);
        $this->assertEqualsWithDelta(250.0, $spending[1]['value'], 0.001);
        $this->assertSame('أخرى', $spending[2]['label']);
        $this->assertEqualsWithDelta(40.0, $spending[2]['value'], 0.001);
    }

    public function test_top_categories_percentages_are_derived_from_the_total_and_sum_to_one_hundred()
    {
        $groceries = Category::factory()->expense()->create(['user_id' => $this->user->id, 'name' => 'بقالة']);
        $restaurants = Category::factory()->expense()->create(['user_id' => $this->user->id, 'name' => 'مطاعم']);
        $bills = Category::factory()->expense()->create(['user_id' => $this->user->id, 'name' => 'فواتير']);

        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 250, now()->startOfMonth()->addDay(), $groceries);
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 500, now()->startOfMonth()->addDay(), $restaurants);
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 250, now()->startOfMonth()->addDay(), $bills);

        $response = $this->actingAs($this->user)->get(route('reports'));

        $summary = $response->inertiaProps('summary');
        $top = $response->inertiaProps('topCategories');

        $this->assertEqualsWithDelta(1000.0, $summary['expense'], 0.001);
        $this->assertCount(3, $top);

        $totalPercent = 0.0;

        foreach ($top as $item) {
            $this->assertArrayHasKey('percent', $item);
            $this->assertEqualsWithDelta(
                round(($item['value'] / $summary['expense']) * 100, 1),
                $item['percent'],
                0.001,
            );
            $totalPercent += $item['percent'];
        }

        $this->assertEqualsWithDelta(100.0, $totalPercent, 0.5);

        $this->assertSame('مطاعم', $top[0]['label']);
        $this->assertEqualsWithDelta(50.0, $top[0]['percent'], 0.001);
    }

    public function test_reports_page_returns_an_empty_state_for_a_new_user()
    {
        $newUser = User::factory()->create();

        $response = $this->actingAs($newUser)->get(route('reports'));
        $response->assertOk();

        $summary = $response->inertiaProps('summary');
        $this->assertEqualsWithDelta(0.0, $summary['expense'], 0.001);
        $this->assertEqualsWithDelta(0.0, $summary['income'], 0.001);
        $this->assertEqualsWithDelta(0.0, $summary['net'], 0.001);

        $this->assertSame([], $response->inertiaProps('spendingByCategory'));
        $this->assertSame([], $response->inertiaProps('topCategories'));

        $trend = $response->inertiaProps('monthlyTrend');
        $this->assertCount(6, $trend);

        foreach ($trend as $entry) {
            $this->assertEqualsWithDelta(0.0, $entry['expense'], 0.001);
            $this->assertEqualsWithDelta(0.0, $entry['income'], 0.001);
        }
    }

    public function test_csv_export_is_downloadable_and_contains_the_monthly_trend()
    {
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 10, now()->startOfMonth()->addDay());
        $this->makeTransaction($this->user, Transaction::TYPE_INCOME, 20, now()->startOfMonth()->addDay());

        $response = $this->actingAs($this->user)->get(route('reports.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('month,income,expense,net', $content);
        $this->assertStringContainsString('20', $content);
        $this->assertStringContainsString('10', $content);
    }

    private function makeTransaction(
        User $user,
        string $type,
        float $amount,
        CarbonInterface $date,
        ?Category $category = null,
    ): Transaction {
        $factory = $type === Transaction::TYPE_INCOME
            ? Transaction::factory()->income()
            : Transaction::factory()->expense();

        return $factory->create([
            'user_id' => $user->id,
            'category_id' => $category?->id,
            'amount' => $amount,
            'date' => $date->toDateString(),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function expectedTrendLabels(): array
    {
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->startOfMonth()->subMonths($i);
            $labels[] = self::MONTH_LABELS[$month->month - 1];
        }

        return $labels;
    }
}
