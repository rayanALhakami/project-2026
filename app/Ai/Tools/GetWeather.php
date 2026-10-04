<?php

namespace App\Ai\Tools;

use App\Models\City;
use App\Services\WeatherService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

class GetWeather implements Tool
{
    /**
     * Create a new tool instance.
     */
    public function __construct(
        private WeatherService $weather = new WeatherService,
    ) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return "Get the current real weather for a Saudi city (temperature, condition, and today's high/low) from live weather data. Use it whenever the user asks about the weather in a city.";
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $cityName = trim((string) $request->string('city'));

        if ($cityName === '') {
            return 'يرجى تحديد اسم المدينة لمعرفة طقسها، مثل: الرياض أو جدة أو أبها.';
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
            $weather = $this->weather->forCity($city);
        } catch (Throwable) {
            return 'تعذر جلب بيانات الطقس حالياً، حاول مرة أخرى بعد قليل.';
        }

        $summary = sprintf(
            'الطقس الآن في %s: %s، %d°، والعظمى اليوم %d° والصغرى %d°.',
            $weather['city'],
            $this->conditionLabel($weather['condition']),
            $weather['temperature'],
            $weather['high'],
            $weather['low'],
        );

        return json_encode([...$weather, 'summary' => $summary], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    /**
     * Get the Arabic label for a weather condition enum value.
     */
    private function conditionLabel(string $condition): string
    {
        return match ($condition) {
            'partly-cloudy' => 'غائم جزئياً',
            'cloudy' => 'غائم',
            'fog' => 'ضباب',
            'rain' => 'مطر',
            'thunder' => 'رعد',
            'hot' => 'حار',
            default => 'صحو',
        };
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
