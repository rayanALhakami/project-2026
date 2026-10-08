<?php

namespace App\Ai\Tools;

use App\Models\City;
use App\Services\PrayerTimesService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

class GetPrayerTimes implements Tool
{
    /**
     * Create a new tool instance.
     */
    public function __construct(
        private PrayerTimesService $prayerTimes = new PrayerTimesService,
    ) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return "Get today's real prayer times (Fajr, Dhuhr, Asr, Maghrib, Isha) for a Saudi city using the Umm al-Qura calculation. Use it whenever the user asks about prayer times, or when planning a day to schedule activities around prayers.";
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $cityName = trim((string) $request->string('city'));

        if ($cityName === '') {
            return 'يرجى تحديد اسم المدينة لمعرفة مواقيت الصلاة، مثل: الرياض أو جدة أو أبها.';
        }

        $city = City::query()
            ->where('name', 'like', "%{$cityName}%")
            ->orWhere('name_en', 'like', "%{$cityName}%")
            ->orderBy('id')
            ->first();

        if (! $city instanceof City) {
            return 'عذراً، لم أجد مدينة باسم «'.$cityName.'» في دليلنا. جرّب مدينة مثل الرياض أو جدة أو العلا أو أبها.';
        }

        try {
            $timings = $this->prayerTimes->forCity($city);
        } catch (Throwable) {
            return 'تعذر جلب مواقيت الصلاة حالياً، حاول مرة أخرى بعد قليل.';
        }

        $summary = sprintf(
            'مواقيت الصلاة اليوم في %s: الفجر %s، الظهر %s، العصر %s، المغرب %s، العشاء %s.',
            $city->name,
            $timings['fajr'],
            $timings['dhuhr'],
            $timings['asr'],
            $timings['maghrib'],
            $timings['isha'],
        );

        return json_encode([
            'city' => $city->name,
            'city_en' => $city->name_en,
            'date' => now()->toDateString(),
            'fajr' => $timings['fajr'],
            'dhuhr' => $timings['dhuhr'],
            'asr' => $timings['asr'],
            'maghrib' => $timings['maghrib'],
            'isha' => $timings['isha'],
            'summary' => $summary,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'city' => $schema->string()
                ->description('Saudi city name in Arabic or English, e.g. "الرياض" or "Riyadh".')
                ->required(),
        ];
    }
}
