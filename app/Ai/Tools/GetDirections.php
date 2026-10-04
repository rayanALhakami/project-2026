<?php

namespace App\Ai\Tools;

use App\Services\DirectionsService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use RuntimeException;
use Stringable;
use Throwable;

class GetDirections implements Tool
{
    /**
     * Create a new tool instance.
     */
    public function __construct(
        private DirectionsService $directions = new DirectionsService,
    ) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Estimate the distance and travel time between two Saudi cities or landmarks: distance in km, car time, flight time when far, and the recommended travel mode (car or flight).';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $from = trim((string) $request->string('from'));
        $to = trim((string) $request->string('to'));

        if ($from === '' || $to === '') {
            return 'يرجى تحديد نقطة الانطلاق والوجهة، مثل: كم المسافة من الرياض إلى جدة؟';
        }

        try {
            $directions = $this->directions->between($from, $to);
        } catch (RuntimeException $exception) {
            return $exception->getMessage();
        } catch (Throwable) {
            return 'تعذر حساب المسافة حالياً، حاول مرة أخرى بعد قليل.';
        }

        $flightNote = $directions['flight_minutes'] !== null
            ? '، أو بالطائرة نحو '.$this->duration($directions['flight_minutes'])
            : '';

        $summary = sprintf(
            'المسافة من %s إلى %s حوالي %s كم، وتستغرق بالسيارة %s%s.',
            $directions['from']['name'],
            $directions['to']['name'],
            number_format($directions['distance_km'], 1),
            $this->duration($directions['car_minutes']),
            $flightNote,
        );

        return json_encode([...$directions, 'summary' => $summary], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'from' => $schema->string()
                ->description('Starting city or landmark name in Arabic or English, e.g. "الرياض" or "Riyadh".')
                ->required(),
            'to' => $schema->string()
                ->description('Destination city or landmark name in Arabic or English, e.g. "جدة" or "Jeddah".')
                ->required(),
        ];
    }

    /**
     * Format a duration in minutes as a short Arabic phrase.
     */
    private function duration(int $minutes): string
    {
        if ($minutes <= 0) {
            return 'أقل من دقيقة';
        }

        if ($minutes < 60) {
            return $this->minutesLabel($minutes);
        }

        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return $rest === 0
            ? $this->hoursLabel($hours)
            : $this->hoursLabel($hours).' و'.$this->minutesLabel($rest);
    }

    /**
     * Format an hour count using the correct Arabic plural form.
     */
    private function hoursLabel(int $hours): string
    {
        return match (true) {
            $hours === 1 => 'ساعة',
            $hours === 2 => 'ساعتين',
            $hours <= 10 => $hours.' ساعات',
            default => $hours.' ساعة',
        };
    }

    /**
     * Format a minute count using the correct Arabic plural form.
     */
    private function minutesLabel(int $minutes): string
    {
        return match (true) {
            $minutes === 1 => 'دقيقة',
            $minutes === 2 => 'دقيقتين',
            $minutes <= 10 => $minutes.' دقائق',
            default => $minutes.' دقيقة',
        };
    }
}
