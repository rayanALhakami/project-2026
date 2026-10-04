<?php

namespace App\Ai\Tools;

use App\Ai\Tools\Concerns\InteractsWithPlaces;
use App\Models\City;
use App\Models\Place;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class EstimateBudget implements Tool
{
    use InteractsWithPlaces;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Estimate the cost of a Saudi trip in SAR: tickets, meals, local transport, and hotel for a given city, number of days, travelers, and travel style (economy, comfort, luxury). Pure calculation over real catalogue prices.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $days = max(1, min(14, $request->integer('days', 1)));
        $travelers = max(1, min(20, $request->integer('travelers', 1)));
        $cityName = trim((string) $request->string('city'));

        $rates = [
            'economy' => ['meals' => 60, 'transport' => 60, 'hotel' => 180, 'label' => 'اقتصادي'],
            'comfort' => ['meals' => 120, 'transport' => 150, 'hotel' => 400, 'label' => 'متوسط'],
            'luxury' => ['meals' => 250, 'transport' => 350, 'hotel' => 900, 'label' => 'فاخر'],
        ];

        $style = (string) $request->string('style');

        if (! isset($rates[$style])) {
            $style = 'comfort';
        }

        $rate = $rates[$style];

        $city = $cityName === ''
            ? null
            : City::query()
                ->where('name', 'like', "%{$cityName}%")
                ->orWhere('name_en', 'like', "%{$cityName}%")
                ->first();

        $places = Place::query()
            ->when($city instanceof City, fn ($query) => $query->where('city_id', $city->id))
            ->orderByDesc('rating')
            ->limit(4)
            ->get();

        $ticketsPerDay = (float) $places->sum(fn (Place $place): float => $this->ticketPrice($place));
        $tickets = round($ticketsPerDay * $days * $travelers, 2);
        $meals = round($rate['meals'] * $days * $travelers, 2);
        $transport = round($rate['transport'] * $days, 2);
        $rooms = (int) max(1, ceil($travelers / 2));
        $hotel = round($rate['hotel'] * $days * $rooms, 2);
        $total = round($tickets + $meals + $transport + $hotel, 2);
        $perPerson = round($total / $travelers, 2);

        $cityLabel = $city instanceof City
            ? $city->name
            : ($cityName === '' ? 'كل المدن المتاحة' : $cityName);

        $notes = 'تقدير بالريال السعودي لـ'.$travelers.' مسافر لمدة '.$days.' يوم بنمط '.$rate['label'].'. '
            .'التذاكر محسوبة على أعلى '.$places->count().' أماكن تقييماً (حتى 4 زيارات يومياً للفرد). '
            .'الوجبات '.$rate['meals'].' ر.س للفرد يومياً، والمواصلات '.$rate['transport'].' ر.س يومياً للمجموعة، '
            .'والإقامة '.$rate['hotel'].' ر.س للغرفة لليلة ('.$rooms.' غرفة). ';

        if ($cityName !== '' && ! $city instanceof City) {
            $notes .= 'لم يتم العثور على مدينة باسم «'.$cityName.'»، تم الحساب على مستوى جميع المدن المتاحة.';
        }

        return $this->encode([
            'city' => $cityLabel,
            'days' => $days,
            'travelers' => $travelers,
            'style' => $style,
            'tickets' => $tickets,
            'meals' => $meals,
            'transport' => $transport,
            'hotel' => $hotel,
            'total' => $total,
            'per_person_total' => $perPerson,
            'currency' => 'SAR',
            'notes' => trim($notes),
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'city' => $schema->string()
                ->description('City name in Arabic or English, e.g. "أبها" or "Abha". Optional; omit for a country-wide estimate.')
                ->nullable(),
            'days' => $schema->integer()
                ->min(1)
                ->max(14)
                ->description('Number of trip days (1 to 14).')
                ->required(),
            'travelers' => $schema->integer()
                ->min(1)
                ->max(20)
                ->default(1)
                ->description('Number of travelers.')
                ->nullable(),
            'style' => $schema->string()
                ->enum(['economy', 'comfort', 'luxury'])
                ->default('comfort')
                ->description('Travel style; defaults to comfort.')
                ->nullable(),
        ];
    }
}
