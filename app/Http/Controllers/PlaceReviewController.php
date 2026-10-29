<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaceReviewController extends Controller
{
    /**
     * List the reviews written about a place.
     */
    public function index(Place $place): JsonResponse
    {
        $reviews = $place->reviews()
            ->latest('reviewed_at')
            ->latest('id')
            ->limit(20)
            ->get();

        $visitorReviews = $place->reviews()->where(function (Builder $query): void {
            $query->whereNull('source')->orWhereNotIn('source', ['demo', 'demo_legacy']);
        });
        $average = (clone $visitorReviews)->avg('rating');

        return response()->json([
            'count' => $visitorReviews->count(),
            'average' => $average === null ? null : round((float) $average, 1),
            'reviews' => $reviews->map(fn (Review $review): array => [
                'id' => $review->id,
                'author' => $review->author,
                'rating' => $review->rating,
                'content' => $review->content,
                'is_demo' => in_array($review->source, ['demo', 'demo_legacy'], true),
                'reviewed_at' => $review->reviewed_at?->toDateString(),
            ])->all(),
        ]);
    }

    /**
     * Create or update the authenticated visitor's review for a place.
     */
    public function store(Request $request, Place $place): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = $place->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'author' => $request->user()->name,
                'rating' => $validated['rating'],
                'content' => $validated['content'] ?? null,
                'source' => 'visitor',
                'reviewed_at' => now(),
            ],
        );

        return response()->json([
            'review' => [
                'id' => $review->id,
                'author' => $review->author,
                'rating' => $review->rating,
                'content' => $review->content,
                'is_demo' => false,
                'reviewed_at' => $review->reviewed_at?->toDateString(),
            ],
        ]);
    }
}
