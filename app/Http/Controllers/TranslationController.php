<?php

namespace App\Http\Controllers;

use App\Ai\Agents\Translator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class TranslationController extends Controller
{
    /**
     * Translate the given text into the requested target language.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:5000'],
            'target_language' => ['required', 'string', 'max:10', 'in:ar,en,fr,es,de,ru,tr,zh,hi,ur'],
            'source_language' => ['nullable', 'string', 'max:10'],
        ]);

        try {
            $response = Translator::make(
                targetLanguage: $validated['target_language'],
                sourceLanguage: $validated['source_language'] ?? null,
            )->prompt($request->string('text'));

            return response()->json([
                'translation' => (string) $response,
                'target_language' => $validated['target_language'],
                'source_language' => $validated['source_language'] ?? null,
            ]);
        } catch (Throwable $exception) {
            Log::error('Translation request failed.', ['exception' => $exception]);

            return response()->json([
                'error' => 'تعذر إتمام الترجمة حالياً، حاول مرة أخرى.',
            ], 503);
        }
    }
}
