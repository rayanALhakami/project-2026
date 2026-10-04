<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Audio;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Transcription;
use Throwable;

class VoiceController extends Controller
{
    /**
     * Transcribe an uploaded voice recording into text.
     */
    public function transcribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'audio' => ['required', 'file', 'max:15360', 'mimetypes:audio/webm,video/webm,audio/ogg,video/ogg,audio/mpeg,audio/mp4,video/mp4,audio/x-m4a,audio/wav,audio/x-wav,audio/wave'],
            'language' => ['nullable', 'string', 'max:10'],
        ]);

        try {
            $pending = Transcription::fromUpload($request->file('audio'));

            if (filled($validated['language'] ?? null)) {
                $pending->language($validated['language']);
            }

            $response = $pending->generate(provider: Lab::Gemini);

            return response()->json([
                'text' => $response->text,
            ]);
        } catch (Throwable $exception) {
            Log::error('Transcription request failed.', ['exception' => $exception]);

            return response()->json([
                'error' => 'تعذر تفريغ التسجيل الصوتي حالياً، حاول مرة أخرى.',
            ], 503);
        }
    }

    /**
     * Generate spoken audio for the given text.
     */
    public function speak(Request $request): JsonResponse|Response
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:2000'],
            'voice' => ['nullable', 'in:male,female'],
            'language' => ['nullable', 'string', 'max:10'],
        ]);

        try {
            $pending = Audio::of($validated['text']);

            match ($validated['voice'] ?? null) {
                'male' => $pending->male(),
                default => $pending->female(),
            };

            $response = $pending->generate(provider: Lab::Gemini);

            return response($response->content(), 200, [
                'Content-Type' => $response->mimeType() ?? 'audio/mpeg',
                'Cache-Control' => 'no-store',
            ]);
        } catch (Throwable $exception) {
            Log::error('Speech synthesis request failed.', ['exception' => $exception]);

            return response()->json([
                'error' => 'تعذر توليد الصوت حالياً، حاول مرة أخرى.',
            ], 503);
        }
    }
}
