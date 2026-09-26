<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_transactions_page_is_displayed()
    {
        $response = $this->actingAs($this->user)->get(route('transactions.index'));

        $response->assertOk();
    }

    public function test_transaction_can_be_stored()
    {
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->post(route('transactions.store'), [
                'title' => 'مشتريات بقالة',
                'amount' => 250,
                'type' => 'expense',
                'category_id' => $category->id,
                'date' => now()->toDateString(),
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'title' => 'مشتريات بقالة',
            'type' => 'expense',
        ]);
    }

    public function test_transaction_can_be_updated()
    {
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);
        $transaction = Transaction::factory()->expense()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->put(route('transactions.update', $transaction), [
                'title' => 'عشاء في مطعم',
                'amount' => 180,
                'type' => 'expense',
                'category_id' => $category->id,
                'date' => now()->toDateString(),
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertSame('عشاء في مطعم', $transaction->refresh()->title);
    }

    public function test_transaction_can_be_deleted()
    {
        $transaction = Transaction::factory()->expense()->create(['user_id' => $this->user->id]);

        $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->delete(route('transactions.destroy', $transaction));

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_transaction_requires_a_valid_type()
    {
        $response = $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->post(route('transactions.store'), [
                'title' => 'اختبار',
                'amount' => 10,
                'type' => 'invalid',
                'date' => now()->toDateString(),
            ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_transaction_cannot_be_updated_by_another_user()
    {
        $other = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);
        $transaction = Transaction::factory()->expense()->create(['user_id' => $other->id]);

        $response = $this
            ->actingAs($this->user)
            ->put(route('transactions.update', $transaction), [
                'title' => 'مخترق',
                'amount' => 1,
                'type' => 'expense',
                'category_id' => $category->id,
                'date' => now()->toDateString(),
            ]);

        $response->assertNotFound();
    }

    public function test_transaction_cannot_be_updated_by_another_user_even_with_invalid_payload()
    {
        $other = User::factory()->create();
        $transaction = Transaction::factory()->expense()->create(['user_id' => $other->id]);

        $response = $this
            ->actingAs($this->user)
            ->put(route('transactions.update', $transaction), [
                'amount' => -5,
                'type' => 'invalid',
            ]);

        $response->assertNotFound();
    }

    public function test_transaction_cannot_be_deleted_by_another_user()
    {
        $other = User::factory()->create();
        $transaction = Transaction::factory()->expense()->create(['user_id' => $other->id]);

        $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->delete(route('transactions.destroy', $transaction))
            ->assertNotFound();

        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
    }

    public function test_index_filters_transactions_by_type()
    {
        $expense = Transaction::factory()->expense()->create(['user_id' => $this->user->id]);
        Transaction::factory()->income()->create(['user_id' => $this->user->id]);

        $payload = $this->indexPayload(['type' => 'expense']);

        $this->assertSame([$expense->id], array_column($payload['data'], 'id'));
        $this->assertSame(1, $payload['total']);
    }

    public function test_index_filters_transactions_by_category()
    {
        $category = Category::factory()->expense()->create([
            'user_id' => $this->user->id,
            'name' => 'فئة أ',
        ]);
        $other = Category::factory()->expense()->create([
            'user_id' => $this->user->id,
            'name' => 'فئة ب',
        ]);

        $matching = Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
        Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'category_id' => $other->id,
        ]);

        $payload = $this->indexPayload(['category' => $category->id]);

        $this->assertSame([$matching->id], array_column($payload['data'], 'id'));
        $this->assertSame(1, $payload['total']);
    }

    public function test_index_filters_transactions_by_search_term()
    {
        $matching = Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'title' => 'قهوة الصباح',
        ]);
        Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'title' => 'عشاء في مطعم',
        ]);

        $payload = $this->indexPayload(['search' => 'قهوة']);

        $this->assertSame([$matching->id], array_column($payload['data'], 'id'));
        $this->assertSame(1, $payload['total']);
    }

    public function test_index_orders_transactions_by_newest_date_then_id_descending()
    {
        $oldest = Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'date' => '2026-01-01',
        ]);
        $newer = Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'date' => '2026-01-05',
        ]);
        $newest = Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'date' => '2026-01-05',
        ]);

        $payload = $this->indexPayload();

        $this->assertSame(
            [$newest->id, $newer->id, $oldest->id],
            array_column($payload['data'], 'id'),
        );
    }

    public function test_index_paginates_transactions_twenty_per_page()
    {
        Transaction::factory()->expense()->count(25)->create(['user_id' => $this->user->id]);

        $first = $this->indexPayload();
        $second = $this->indexPayload(['page' => 2]);

        $this->assertCount(20, $first['data']);
        $this->assertSame(25, $first['total']);
        $this->assertCount(5, $second['data']);
        $this->assertSame(25, $second['total']);
    }

    public function test_index_never_exposes_another_users_transactions()
    {
        $mine = Transaction::factory()->expense()->create(['user_id' => $this->user->id]);

        $other = User::factory()->create();
        Transaction::factory()->income()->create(['user_id' => $other->id]);

        $payload = $this->indexPayload();

        $this->assertSame([$mine->id], array_column($payload['data'], 'id'));
        $this->assertSame(1, $payload['total']);
    }

    public function test_transaction_title_is_optional()
    {
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->post(route('transactions.store'), [
                'amount' => 10,
                'type' => 'expense',
                'category_id' => $category->id,
                'date' => now()->toDateString(),
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseCount('transactions', 1);
        $this->assertNull(Transaction::firstOrFail()->title);
    }

    public function test_transaction_requires_a_category()
    {
        $response = $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->post(route('transactions.store'), [
                'title' => 'اختبار',
                'amount' => 10,
                'type' => 'expense',
                'date' => now()->toDateString(),
            ]);

        $response->assertSessionHasErrors('category_id');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_categories_are_ordered_by_recent_usage()
    {
        $first = Category::factory()->expense()->create([
            'user_id' => $this->user->id,
            'name' => 'فئة أولى',
        ]);
        $second = Category::factory()->expense()->create([
            'user_id' => $this->user->id,
            'name' => 'فئة ثانية',
        ]);

        Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'category_id' => $first->id,
        ]);
        Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'category_id' => $second->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('transactions.index'));

        $response->assertOk();

        $categories = $response->viewData('page')['props']['categories'];

        $this->assertSame(
            [$second->id, $first->id],
            array_column($categories, 'id'),
        );
    }

    public function test_transaction_amount_must_be_greater_than_zero()
    {
        $response = $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->post(route('transactions.store'), [
                'title' => 'اختبار',
                'amount' => 0,
                'type' => 'expense',
                'date' => now()->toDateString(),
            ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_transaction_requires_a_date()
    {
        $response = $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->post(route('transactions.store'), [
                'title' => 'اختبار',
                'amount' => 10,
                'type' => 'expense',
            ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_transaction_date_must_be_valid()
    {
        $response = $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->post(route('transactions.store'), [
                'title' => 'اختبار',
                'amount' => 10,
                'type' => 'expense',
                'date' => 'not-a-date',
            ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_transaction_category_must_belong_to_the_authenticated_user()
    {
        $other = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $other->id]);

        $response = $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->post(route('transactions.store'), [
                'title' => 'اختبار',
                'amount' => 10,
                'type' => 'expense',
                'category_id' => $category->id,
                'date' => now()->toDateString(),
            ]);

        $response->assertSessionHasErrors('category_id');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_expense_cannot_use_an_income_category()
    {
        $category = Category::factory()->income()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->post(route('transactions.store'), [
                'title' => 'اختبار',
                'amount' => 10,
                'type' => 'expense',
                'category_id' => $category->id,
                'date' => now()->toDateString(),
            ]);

        $response->assertSessionHasErrors('category_id');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_income_cannot_use_an_expense_category()
    {
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->post(route('transactions.store'), [
                'title' => 'اختبار',
                'amount' => 10,
                'type' => 'income',
                'category_id' => $category->id,
                'date' => now()->toDateString(),
            ]);

        $response->assertSessionHasErrors('category_id');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_transaction_store_assigns_the_authenticated_user_id()
    {
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);

        $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->post(route('transactions.store'), [
                'title' => 'مشتريات بقالة',
                'amount' => 250,
                'type' => 'expense',
                'category_id' => $category->id,
                'date' => now()->toDateString(),
            ]);

        $transaction = Transaction::firstOrFail();

        $this->assertSame($this->user->id, $transaction->user_id);
    }

    public function test_transaction_store_ignores_a_spoofed_user_id()
    {
        $other = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);

        $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->post(route('transactions.store'), [
                'title' => 'مشتريات بقالة',
                'amount' => 250,
                'type' => 'expense',
                'category_id' => $category->id,
                'user_id' => $other->id,
                'date' => now()->toDateString(),
            ]);

        $transaction = Transaction::firstOrFail();

        $this->assertSame($this->user->id, $transaction->user_id);
    }

    public function test_transaction_update_does_not_change_the_owner()
    {
        $other = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);
        $transaction = Transaction::factory()->expense()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->from(route('transactions.index'))
            ->put(route('transactions.update', $transaction), [
                'title' => 'محدث',
                'amount' => 50,
                'type' => 'expense',
                'category_id' => $category->id,
                'user_id' => $other->id,
                'date' => now()->toDateString(),
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertSame($this->user->id, $transaction->refresh()->user_id);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{data: array<int, array<string, mixed>>, total: int}
     */
    private function indexPayload(array $query = []): array
    {
        $response = $this->actingAs($this->user)->get(route('transactions.index', $query));

        $response->assertOk();

        return $response->viewData('page')['props']['transactions'];
    }
}
