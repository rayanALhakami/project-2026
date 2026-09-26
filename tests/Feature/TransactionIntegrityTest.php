<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransactionIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_transaction_cannot_use_a_category_owned_by_another_user()
    {
        $other = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $other->id]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('الفئة غير صالحة.');

        Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_transaction_cannot_use_a_category_of_a_different_type()
    {
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('نوع الفئة لا يطابق نوع المعاملة.');

        Transaction::factory()->income()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_transaction_can_use_a_matching_category_and_type()
    {
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);

        $transaction = Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => Transaction::TYPE_EXPENSE,
        ]);
    }

    public function test_deleting_a_category_nulls_the_category_on_its_transactions()
    {
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);
        $transaction = Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);

        $category->delete();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'category_id' => null,
        ]);
        $this->assertNull($transaction->refresh()->category_id);
    }

    public function test_deleting_a_user_cascades_to_transactions_and_categories()
    {
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);
        $transaction = Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);

        $this->user->delete();

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
