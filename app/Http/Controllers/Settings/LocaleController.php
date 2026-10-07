<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    /**
     * Update the authenticated user's preferred locale.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(config('app.supported_locales'))],
        ]);

        $request->user()->update(['locale' => $validated['locale']]);

        return response()->json([
            'locale' => $validated['locale'],
        ]);
    }
}
