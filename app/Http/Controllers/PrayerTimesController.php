<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Services\PrayerTimesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class PrayerTimesController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private PrayerTimesService $prayerTimes,
    ) {}

    /**
     * Return today's prayer times for a single city.
     */
    public function show(City $city): JsonResponse
    {
        try {
            return response()->json($this->prayerTimes->forCity($city));
        } catch (Throwable $exception) {
            Log::error('Prayer times request failed.', [
                'exception' => $exception,
                'city_id' => $city->id,
            ]);

            return response()->json(['error' => 'تعذر جلب البيانات حالياً، حاول مرة أخرى.'], 503);
        }
    }
}
