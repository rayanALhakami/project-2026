<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\CitySeeder;
use Database\Seeders\PlaceSeeder;
use Database\Seeders\ReviewSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviews_endpoint_returns_an_empty_summary(): void
    {
        $place = Place::factory()->create();

        $this->getJson(route('places.reviews.index', $place))
            ->assertOk()
            ->assertExactJson([
                'count' => 0,
                'average' => null,
                'reviews' => [],
            ]);
    }

    public function test_reviews_endpoint_lists_reviews_and_average(): void
    {
        $place = Place::factory()->create();

        Review::factory()->for($place)->create([
            'author' => 'أقدم مراجعة',
            'rating' => 5,
            'reviewed_at' => now()->subDays(3),
        ]);
        Review::factory()->for($place)->create([
            'author' => 'المراجعة الوسطى',
            'rating' => 4,
            'reviewed_at' => now()->subDays(2),
        ]);
        Review::factory()->for($place)->create([
            'author' => 'أحدث مراجعة',
            'rating' => 3,
            'reviewed_at' => now()->subDay(),
        ]);

        $this->getJson(route('places.reviews.index', $place))
            ->assertOk()
            ->assertJsonPath('count', 3)
            ->assertJsonPath('average', 4)
            ->assertJsonPath('reviews.0.author', 'أحدث مراجعة')
            ->assertJsonPath('reviews.1.author', 'المراجعة الوسطى')
            ->assertJsonPath('reviews.2.author', 'أقدم مراجعة');
    }

    public function test_reviews_endpoint_limits_the_list_to_twenty_but_reports_the_full_count(): void
    {
        $place = Place::factory()->create();

        Review::factory()->for($place)->count(25)->create(['rating' => 4]);

        $this->getJson(route('places.reviews.index', $place))
            ->assertOk()
            ->assertJsonCount(20, 'reviews')
            ->assertJsonPath('count', 25)
            ->assertJsonPath('average', 4);
    }

    public function test_guests_cannot_submit_reviews(): void
    {
        $place = Place::factory()->create();

        $this->post(route('places.reviews.store', $place), [
            'rating' => 5,
            'content' => 'مكان رائع',
        ])->assertRedirect(route('login'));
    }

    public function test_demo_reviews_are_labelled_and_excluded_from_the_visitor_summary(): void
    {
        $place = Place::factory()->create();
        Review::factory()->for($place)->create(['source' => 'demo', 'rating' => 5]);
        Review::factory()->for($place)->create(['source' => 'demo_legacy', 'rating' => 5]);
        Review::factory()->for($place)->create(['source' => 'manual', 'rating' => 2]);

        $response = $this->getJson(route('places.reviews.index', $place))
            ->assertJsonPath('count', 1)
            ->assertJsonPath('average', 2)
            ->assertJsonCount(3, 'reviews');

        $this->assertCount(2, array_filter($response->json('reviews'), fn (array $review): bool => $review['is_demo']));
    }

    public function test_legacy_demo_migration_preserves_real_and_unmatched_reviews(): void
    {
        $this->seed(CitySeeder::class);
        $this->seed(PlaceSeeder::class);
        $this->seed(ReviewSeeder::class);
        $sample = Review::query()->firstOrFail();
        Review::query()->update(['source' => 'visitor']);
        $real = Review::factory()->create([
            'user_id' => User::factory(),
            'source' => 'visitor',
            'author' => $sample->author,
            'content' => $sample->content,
        ]);
        $unmatched = Review::factory()->create(['source' => 'visitor', 'content' => 'Not a seeded review']);
        $migration = require database_path('migrations/2026_10_29_170923_mark_seeded_reviews_as_demo.php');

        $migration->up();

        $this->assertSame('demo_legacy', $sample->fresh()->source);
        $this->assertSame('visitor', $real->fresh()->source);
        $this->assertSame('visitor', $unmatched->fresh()->source);

        $migration->down();

        $this->assertSame('visitor', $sample->fresh()->source);
    }

    public function test_authenticated_users_create_a_review(): void
    {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $this->actingAs($user)
            ->postJson(route('places.reviews.store', $place), [
                'rating' => 5,
                'content' => 'تجربة ممتازة',
            ])
            ->assertOk()
            ->assertJsonPath('review.author', $user->name)
            ->assertJsonPath('review.rating', 5)
            ->assertJsonPath('review.content', 'تجربة ممتازة');

        $this->assertDatabaseHas('reviews', [
            'place_id' => $place->id,
            'user_id' => $user->id,
            'rating' => 5,
            'source' => 'visitor',
        ]);
    }

    public function test_submitting_again_updates_the_same_review(): void
    {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $this->actingAs($user)
            ->postJson(route('places.reviews.store', $place), ['rating' => 4])
            ->assertOk();

        $this->actingAs($user)
            ->postJson(route('places.reviews.store', $place), ['rating' => 2])
            ->assertOk();

        $this->assertDatabaseCount('reviews', 1);

        $review = Review::query()->firstOrFail();

        $this->assertSame(2, $review->fresh()->rating);
        $this->assertSame($user->id, $review->user_id);
    }

    public function test_review_submission_is_validated(): void
    {
        $user = User::factory()->create();
        $place = Place::factory()->create();

        $this->actingAs($user)
            ->postJson(route('places.reviews.store', $place), ['content' => 'بدون تقييم'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);

        $this->actingAs($user)
            ->postJson(route('places.reviews.store', $place), ['rating' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);

        $this->actingAs($user)
            ->postJson(route('places.reviews.store', $place), ['rating' => 6])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);

        $this->actingAs($user)
            ->postJson(route('places.reviews.store', $place), [
                'rating' => 5,
                'content' => str_repeat('ا', 1001),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['content']);

        $this->assertDatabaseCount('reviews', 0);
    }
}
