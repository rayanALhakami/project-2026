<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Place;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * List the place ids favorited by the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'ids' => $this->favoriteIds($request),
        ]);
    }

    /**
     * Toggle a place in the authenticated user's favorites.
     */
    public function toggle(Request $request, Place $place): JsonResponse
    {
        $favorite = $request->user()->favorites()
            ->where('place_id', $place->id)
            ->first();

        if ($favorite !== null) {
            $favorite->delete();
            $favorited = false;
        } else {
            try {
                $request->user()->favorites()->firstOrCreate([
                    'place_id' => $place->id,
                ]);
            } catch (UniqueConstraintViolationException) {
                // A concurrent request already stored the same favorite.
            }

            $favorited = true;
        }

        return response()->json([
            'favorited' => $favorited,
            'ids' => $this->favoriteIds($request),
        ]);
    }

    /**
     * Merge guest favorites stored in the browser into the user's account.
     */
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['present', 'array'],
            'ids.*' => ['integer', 'exists:places,id'],
        ]);

        /** @var array<int, int> $ids */
        $ids = array_values(array_unique($validated['ids']));

        if ($ids !== []) {
            $existing = $request->user()->favorites()
                ->whereIn('place_id', $ids)
                ->pluck('place_id')
                ->all();

            $missing = array_values(array_diff($ids, $existing));

            if ($missing !== []) {
                $now = now();

                Favorite::query()->insertOrIgnore(array_map(
                    fn (int $placeId): array => [
                        'user_id' => $request->user()->id,
                        'place_id' => $placeId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    $missing,
                ));
            }
        }

        return response()->json([
            'ids' => $this->favoriteIds($request),
        ]);
    }

    /**
     * Get the authenticated user's favorited place ids.
     *
     * @return array<int, int>
     */
    private function favoriteIds(Request $request): array
    {
        return $request->user()->favorites()
            ->orderBy('place_id')
            ->pluck('place_id')
            ->all();
    }
}
