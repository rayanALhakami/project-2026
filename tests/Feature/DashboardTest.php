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

class DashboardTest extends TestCase
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
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->hasAll(['summary', 'monthlyTrend', 'spendingByCategory', 'categoryRanking', 'recentTransactions', 'categories', 'insight'])
        );
    }

    public function test_summary_only_includes_the_users_current_month_transactions()
    {
        $other = User::factory()->create();
        $thisMonth = now()->startOfMonth()->addDay();

        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 300, $thisMonth);
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 200, $thisMonth);
        $this->makeTransaction($this->user, Transaction::TYPE_INCOME, 1000, $thisMonth);

        // Same user, previous month: must be excluded from the current-month summary.
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 999, $this->previousMonthDate());
        // Another user, current month: must be excluded entirely.
        $this->makeTransaction($other, Transaction::TYPE_EXPENSE, 777, $thisMonth);
        $this->makeTransaction($other, Transaction::TYPE_INCOME, 555, $thisMonth);

        $summary = $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->inertiaProps('summary');

        $this->assertEqualsWithDelta(500.0, $summary['spent'], 0.001);
        $this->assertEqualsWithDelta(1000.0, $summary['income'], 0.001);
        $this->assertEqualsWithDelta(500.0, $summary['balance'], 0.001);
    }

    public function test_spent_delta_is_computed_against_the_previous_month()
    {
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 500, now()->startOfMonth()->addDay());
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 999, $this->previousMonthDate());

        $summary = $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->inertiaProps('summary');

        $this->assertEqualsWithDelta(-49.9, $summary['spent_delta'], 0.001);
    }

    public function test_spent_delta_is_null_when_there_is_no_previous_month_spending()
    {
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 500, now()->startOfMonth()->addDay());

        $summary = $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->inertiaProps('summary');

        $this->assertNull($summary['spent_delta']);
    }

    public function test_monthly_trend_returns_six_chronological_months_with_the_current_month_total_last()
    {
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 250, now()->startOfMonth()->addDay());
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 150, now()->startOfMonth()->addDays(3));
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 100, $this->previousMonthDate());

        $trend = $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->inertiaProps('monthlyTrend');

        $this->assertCount(6, $trend);

        foreach ($trend as $entry) {
            $this->assertArrayHasKey('label', $entry);
            $this->assertArrayHasKey('value', $entry);
        }

        $this->assertSame($this->expectedTrendLabels(), array_column($trend, 'label'));

        $this->assertEqualsWithDelta(0.0, $trend[0]['value'], 0.001);
        $this->assertEqualsWithDelta(0.0, $trend[3]['value'], 0.001);
        $this->assertEqualsWithDelta(100.0, $trend[4]['value'], 0.001);
        $this->assertEqualsWithDelta(400.0, $trend[5]['value'], 0.001);
    }

    public function test_spending_by_category_groups_sorts_and_falls_back_for_uncategorized_expenses()
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
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 50, now()->startOfMonth()->addDays(2), $groceries);
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 200, now()->startOfMonth()->addDays(3), $restaurants);
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 25, now()->startOfMonth()->addDays(4));
        // Previous month: excluded.
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 999, $this->previousMonthDate(), $groceries);

        $spending = $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->inertiaProps('spendingByCategory');

        $this->assertCount(3, $spending);

        $this->assertSame('مطاعم', $spending[0]['label']);
        $this->assertEqualsWithDelta(200.0, $spending[0]['value'], 0.001);
        $this->assertSame('#ff9500', $spending[0]['color']);
        $this->assertSame('🍽️', $spending[0]['icon']);

        $this->assertSame('بقالة', $spending[1]['label']);
        $this->assertEqualsWithDelta(150.0, $spending[1]['value'], 0.001);
        $this->assertSame('#34c759', $spending[1]['color']);
        $this->assertSame('🛒', $spending[1]['icon']);

        $this->assertSame('أخرى', $spending[2]['label']);
        $this->assertEqualsWithDelta(25.0, $spending[2]['value'], 0.001);
        $this->assertSame('#8e8e93', $spending[2]['color']);
        $this->assertSame('🏷️', $spending[2]['icon']);
    }

    public function test_category_ranking_orders_all_expense_categories_by_current_month_spend()
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
        $unused = Category::factory()->expense()->create([
            'user_id' => $this->user->id,
            'name' => 'ترفيه',
        ]);
        Category::factory()->income()->create([
            'user_id' => $this->user->id,
            'name' => 'راتب',
        ]);

        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 100, now()->startOfMonth()->addDay(), $groceries);
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 50, now()->startOfMonth()->addDays(2), $groceries);
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 200, now()->startOfMonth()->addDays(3), $restaurants);
        // Previous month: excluded from the ranking totals.
        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 999, $this->previousMonthDate(), $restaurants);

        $ranking = $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->inertiaProps('categoryRanking');

        $this->assertCount(3, $ranking);

        $this->assertSame($restaurants->id, $ranking[0]['id']);
        $this->assertSame('مطاعم', $ranking[0]['label']);
        $this->assertEqualsWithDelta(200.0, $ranking[0]['value'], 0.001);
        $this->assertEqualsWithDelta(57.1, $ranking[0]['percent'], 0.001);

        $this->assertSame($groceries->id, $ranking[1]['id']);
        $this->assertSame('بقالة', $ranking[1]['label']);
        $this->assertEqualsWithDelta(150.0, $ranking[1]['value'], 0.001);
        $this->assertEqualsWithDelta(42.9, $ranking[1]['percent'], 0.001);

        $this->assertSame($unused->id, $ranking[2]['id']);
        $this->assertEqualsWithDelta(0.0, $ranking[2]['value'], 0.001);
        $this->assertEqualsWithDelta(0.0, $ranking[2]['percent'], 0.001);
    }

    public function test_recent_transactions_are_limited_to_eight_and_sorted_newest_first()
    {
        $category = Category::factory()->expense()->create([
            'user_id' => $this->user->id,
            'name' => 'بقالة',
            'color' => '#34c759',
            'icon' => '🛒',
        ]);

        for ($i = 0; $i < 10; $i++) {
            $this->makeTransaction(
                $this->user,
                Transaction::TYPE_EXPENSE,
                10 + $i,
                now()->startOfDay()->subDays($i),
                $i === 0 ? $category : null,
            );
        }

        $recent = $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->inertiaProps('recentTransactions');

        $this->assertCount(8, $recent);

        $dates = array_column($recent, 'date');
        $sorted = $dates;
        rsort($sorted);
        $this->assertSame($sorted, $dates);

        $this->assertSame(now()->toDateString(), $recent[0]['date']);
        $this->assertSame(now()->subDays(7)->toDateString(), $recent[7]['date']);

        foreach ($recent as $transaction) {
            $this->assertSame(
                ['id', 'title', 'amount', 'type', 'date', 'category'],
                array_keys($transaction),
            );
        }

        $this->assertSame(Transaction::TYPE_EXPENSE, $recent[0]['type']);
        $this->assertEqualsWithDelta(10.0, $recent[0]['amount'], 0.001);
        $this->assertSame($category->id, $recent[0]['category']['id']);
        $this->assertSame('بقالة', $recent[0]['category']['name']);
        $this->assertNull($recent[1]['category']);
    }

    public function test_insight_is_null_without_current_month_expenses()
    {
        $insight = $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->inertiaProps('insight');

        $this->assertNull($insight);
    }

    public function test_insight_is_returned_when_there_is_a_current_month_expense()
    {
        $category = Category::factory()->expense()->create([
            'user_id' => $this->user->id,
            'name' => 'مطاعم',
        ]);

        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 120, now()->startOfMonth()->addDay(), $category);

        $insight = $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->inertiaProps('insight');

        $this->assertIsString($insight);
        $this->assertStringContainsString('مطاعم', $insight);
    }

    public function test_dashboard_data_is_isolated_per_user()
    {
        $other = User::factory()->create();
        $this->makeTransaction($other, Transaction::TYPE_EXPENSE, 4321, now()->startOfMonth()->addDay());
        $this->makeTransaction($other, Transaction::TYPE_INCOME, 1234, now()->startOfMonth()->addDay());

        $this->makeTransaction($this->user, Transaction::TYPE_EXPENSE, 100, now()->startOfMonth()->addDay());

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $summary = $response->inertiaProps('summary');
        $recent = $response->inertiaProps('recentTransactions');

        $this->assertEqualsWithDelta(100.0, $summary['spent'], 0.001);
        $this->assertEqualsWithDelta(0.0, $summary['income'], 0.001);

        $this->assertCount(1, $recent);
        $this->assertEqualsWithDelta(100.0, $recent[0]['amount'], 0.001);
        $this->assertSame(Transaction::TYPE_EXPENSE, $recent[0]['type']);
    }

    public function test_dashboard_returns_an_empty_state_for_a_user_without_data()
    {
        $newUser = User::factory()->create();

        $response = $this->actingAs($newUser)->get(route('dashboard'));
        $response->assertOk();

        $summary = $response->inertiaProps('summary');
        $this->assertEqualsWithDelta(0.0, $summary['spent'], 0.001);
        $this->assertEqualsWithDelta(0.0, $summary['income'], 0.001);
        $this->assertEqualsWithDelta(0.0, $summary['balance'], 0.001);
        $this->assertNull($summary['spent_delta']);

        $this->assertSame([], $response->inertiaProps('spendingByCategory'));
        $this->assertSame([], $response->inertiaProps('categoryRanking'));
        $this->assertSame([], $response->inertiaProps('recentTransactions'));
        $this->assertNull($response->inertiaProps('insight'));

        $trend = $response->inertiaProps('monthlyTrend');
        $this->assertCount(6, $trend);

        foreach ($trend as $entry) {
            $this->assertEqualsWithDelta(0.0, $entry['value'], 0.001);
        }
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

    private function previousMonthDate(): CarbonInterface
    {
        return now()->copy()->startOfMonth()->subMonth()->addDay();
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
