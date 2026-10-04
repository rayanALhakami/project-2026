<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class WeatherController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private WeatherService $weather,
    ) {}

    /**
     * Return the current weather for every city.
     */
    public function index(): JsonResponse
    {
        try {
            return response()->json($this->weather->forAllCities());
        } catch (Throwable $exception) {
            Log::error('Weather index request failed.', ['exception' => $exception]);

            return response()->json(['error' => 'تعذر جلب البيانات حالياً، حاول مرة أخرى.'], 503);
        }
    }

    /**
     * Return the current weather for a single city.
     */
    public function show(City $city): JsonResponse
    {
        try {
            return response()->json($this->weather->forCity($city));
        } catch (Throwable $exception) {
            Log::error('Weather show request failed.', [
                'exception' => $exception,
                'city_id' => $city->id,
            ]);

            return response()->json(['error' => 'تعذر جلب البيانات حالياً، حاول مرة أخرى.'], 503);
        }
    }
}
