<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_categories_page_is_displayed()
    {
        $response = $this->actingAs($this->user)->get(route('categories.index'));

        $response->assertOk();
    }

    public function test_category_can_be_stored()
    {
        $response = $this
            ->actingAs($this->user)
            ->from(route('categories.index'))
            ->post(route('categories.store'), [
                'name' => 'تسوق',
                'type' => 'expense',
                'color' => '#5856d6',
                'icon' => '🛍️',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'user_id' => $this->user->id,
            'name' => 'تسوق',
            'type' => 'expense',
        ]);
    }

    public function test_duplicate_category_name_is_rejected_for_the_same_user_and_type()
    {
        Category::factory()->expense()->create([
            'user_id' => $this->user->id,
            'name' => 'مطاعم',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->from(route('categories.index'))
            ->post(route('categories.store'), [
                'name' => 'مطاعم',
                'type' => 'expense',
                'color' => '#ff9500',
                'icon' => '🍽️',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_category_name_can_be_reused_for_a_different_type()
    {
        Category::factory()->expense()->create([
            'user_id' => $this->user->id,
            'name' => 'مكافأة',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->from(route('categories.index'))
            ->post(route('categories.store'), [
                'name' => 'مكافأة',
                'type' => 'income',
                'color' => '#34c759',
                'icon' => '🎁',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'user_id' => $this->user->id,
            'name' => 'مكافأة',
            'type' => 'income',
        ]);
    }

    public function test_category_can_be_updated()
    {
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->from(route('categories.index'))
            ->put(route('categories.update', $category), [
                'name' => 'مطاعم',
                'type' => 'expense',
                'color' => '#ff9500',
                'icon' => '🍽️',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertSame('مطاعم', $category->refresh()->name);
    }

    public function test_category_type_cannot_be_changed_when_it_has_transactions()
    {
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);

        Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->from(route('categories.index'))
            ->put(route('categories.update', $category), [
                'name' => 'مطاعم',
                'type' => 'income',
                'color' => '#ff9500',
                'icon' => '🍽️',
            ]);

        $response->assertSessionHasErrors('type');

        $this->assertSame('expense', $category->refresh()->type);
    }

    public function test_category_type_can_be_changed_when_it_has_no_transactions()
    {
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);

        $response = $this
            ->actingAs($this->user)
            ->from(route('categories.index'))
            ->put(route('categories.update', $category), [
                'name' => 'راتب',
                'type' => 'income',
                'color' => '#34c759',
                'icon' => '💼',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertSame('income', $category->refresh()->type);
    }

    public function test_category_can_be_deleted()
    {
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);

        $this
            ->actingAs($this->user)
            ->from(route('categories.index'))
            ->delete(route('categories.destroy', $category));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_cannot_be_updated_by_another_user()
    {
        $other = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $other->id]);

        $response = $this
            ->actingAs($this->user)
            ->put(route('categories.update', $category), [
                'name' => 'مخترق',
                'type' => 'expense',
                'color' => '#000000',
            ]);

        $response->assertNotFound();
    }

    public function test_category_cannot_be_deleted_by_another_user()
    {
        $other = User::factory()->create();
        $category = Category::factory()->expense()->create(['user_id' => $other->id]);

        $this
            ->actingAs($this->user)
            ->from(route('categories.index'))
            ->delete(route('categories.destroy', $category))
            ->assertNotFound();

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_deleting_a_category_nulls_the_category_id_on_its_transactions()
    {
        $category = Category::factory()->expense()->create(['user_id' => $this->user->id]);
        $transaction = Transaction::factory()->expense()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);

        $this
            ->actingAs($this->user)
            ->from(route('categories.index'))
            ->delete(route('categories.destroy', $category));

        $this->assertNull($transaction->refresh()->category_id);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'category_id' => null,
        ]);
    }

    public function test_category_store_assigns_the_authenticated_user_id()
    {
        $other = User::factory()->create();

        $this
            ->actingAs($this->user)
            ->from(route('categories.index'))
            ->post(route('categories.store'), [
                'name' => 'تسوق',
                'type' => 'expense',
                'color' => '#5856d6',
                'icon' => '🛍️',
                'user_id' => $other->id,
            ]);

        $category = Category::where('name', 'تسوق')->firstOrFail();

        $this->assertSame($this->user->id, $category->user_id);
    }

    public function test_category_requires_a_valid_type()
    {
        $response = $this
            ->actingAs($this->user)
            ->from(route('categories.index'))
            ->post(route('categories.store'), [
                'name' => 'اختبار',
                'type' => 'invalid',
                'color' => '#000000',
            ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_category_color_must_be_a_hex_value()
    {
        $response = $this
            ->actingAs($this->user)
            ->from(route('categories.index'))
            ->post(route('categories.store'), [
                'name' => 'اختبار',
                'type' => 'expense',
                'color' => 'red',
            ]);

        $response->assertSessionHasErrors('color');
    }

    public function test_category_icon_cannot_exceed_sixteen_characters()
    {
        $response = $this
            ->actingAs($this->user)
            ->from(route('categories.index'))
            ->post(route('categories.store'), [
                'name' => 'اختبار',
                'type' => 'expense',
                'color' => '#000000',
                'icon' => str_repeat('a', 17),
            ]);

        $response->assertSessionHasErrors('icon');
    }

    public function test_category_name_cannot_exceed_255_characters()
    {
        $response = $this
            ->actingAs($this->user)
            ->from(route('categories.index'))
            ->post(route('categories.store'), [
                'name' => str_repeat('ا', 256),
                'type' => 'expense',
                'color' => '#000000',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_categories_index_returns_only_the_authenticated_users_categories_with_transaction_counts()
    {
        $category = Category::factory()->expense()->create([
            'user_id' => $this->user->id,
            'name' => 'بقالة',
        ]);

        Transaction::factory()->expense()->count(2)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);

        $other = User::factory()->create();
        Category::factory()->income()->create([
            'user_id' => $other->id,
            'name' => 'راتب',
        ]);

        $categories = $this->indexPayload();

        $this->assertCount(1, $categories);
        $this->assertSame('بقالة', $categories[0]['name']);
        $this->assertSame(2, (int) $categories[0]['transactions_count']);
    }

    public function test_categories_index_orders_by_type_then_name()
    {
        Category::factory()->income()->create([
            'user_id' => $this->user->id,
            'name' => 'Beta',
        ]);
        Category::factory()->expense()->create([
            'user_id' => $this->user->id,
            'name' => 'Zeta',
        ]);
        Category::factory()->expense()->create([
            'user_id' => $this->user->id,
            'name' => 'Alpha',
        ]);

        $categories = $this->indexPayload();

        $this->assertSame(
            ['Alpha', 'Zeta', 'Beta'],
            array_column($categories, 'name'),
        );
    }

    public function test_registering_a_user_creates_the_default_categories()
    {
        $response = $this->post(route('register'), [
            'name' => 'مستخدم جديد',
            'email' => 'new-user@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors();

        $user = User::where('email', 'new-user@example.com')->firstOrFail();

        $this->assertSame(12, $user->categories()->count());

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'بقالة',
            'type' => 'expense',
        ]);

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'راتب',
            'type' => 'income',
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function indexPayload(): array
    {
        $response = $this->actingAs($this->user)->get(route('categories.index'));

        $response->assertOk();

        return $response->viewData('page')['props']['categories'];
    }
}
