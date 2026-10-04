<?php

namespace Tests\Feature\Admin;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_delete_a_review(): void
    {
        $admin = User::factory()->admin()->create();
        $review = Review::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.reviews.destroy', $review))
            ->assertRedirect();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_regular_users_cannot_delete_reviews(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $this->actingAs($user)
            ->delete(route('admin.reviews.destroy', $review))
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
