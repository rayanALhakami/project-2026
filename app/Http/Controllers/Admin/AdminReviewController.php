<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminReviewController extends Controller
{
    /**
     * List the reviews available for moderation.
     */
    public function index(): Response
    {
        $reviews = Review::query()
            ->with('place')
            ->orderByDesc('reviewed_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Review $review): array => [
                'id' => $review->id,
                'place_name' => $review->place?->name,
                'author' => $review->author,
                'rating' => $review->rating,
                'content' => $review->content,
                'source' => $review->source,
                'reviewed_at' => $review->reviewed_at?->toDateString(),
            ])
            ->all();

        return Inertia::render('Admin/Reviews', [
            'reviews' => $reviews,
        ]);
    }

    /**
     * Delete a review.
     */
    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back();
    }
}
