<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class PrayerTimesService
{
    /**
     * Get today's prayer times for a city using the Umm al-Qura calculation.
     *
     * @return array{cityId: int, fajr: string, dhuhr: string, asr: string, maghrib: string, isha: string}
     */
    public function forCity(City $city): array
    {
        $date = now()->toDateString();

        /** @var array{cityId: int, fajr: string, dhuhr: string, asr: string, maghrib: string, isha: string} $prayerTimes */
        $prayerTimes = Cache::remember(
            "prayer:{$city->id}:{$date}",
            now()->endOfDay(),
            fn (): array => $this->fetch($city, $date),
        );

        return $prayerTimes;
    }

    /**
     * Fetch and shape the prayer times returned by the Aladhan API.
     *
     * @return array{cityId: int, fajr: string, dhuhr: string, asr: string, maghrib: string, isha: string}
     */
    private function fetch(City $city, string $date): array
    {
        try {
            $response = Http::timeout(8)
                ->retry(2, 200)
                ->get("https://api.aladhan.com/v1/timings/{$date}", [
                    'latitude' => $city->latitude,
                    'longitude' => $city->longitude,
                    'method' => 4,
                ]);
        } catch (Throwable $exception) {
            throw new RuntimeException('تعذر الاتصال بخدمة مواقيت الصلاة، حاول مرة أخرى بعد قليل.', 0, $exception);
        }

        if (! $response->successful()) {
            throw new RuntimeException('تعذر جلب مواقيت الصلاة حالياً، حاول مرة أخرى بعد قليل.');
        }

        $timings = $response->json('data.timings');

        if (! is_array($timings)) {
            throw new RuntimeException('بيانات مواقيت الصلاة المستلمة غير صالحة.');
        }

        $time = function (string $key) use ($timings): string {
            $value = trim((string) ($timings[$key] ?? ''));

            return trim(explode(' ', $value, 2)[0]);
        };

        return [
            'cityId' => $city->id,
            'fajr' => $time('Fajr'),
            'dhuhr' => $time('Dhuhr'),
            'asr' => $time('Asr'),
            'maghrib' => $time('Maghrib'),
            'isha' => $time('Isha'),
        ];
    }
}
