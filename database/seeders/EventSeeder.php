<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Seed the seasonal events and festivals of the catalogue.
     *
     * Dates are relative to the seeding day so the catalogue never goes stale.
     * The offsets are fixed per event to keep the seeder deterministic.
     */
    public function run(): void
    {
        $cityIds = City::query()->pluck('id', 'name_en');
        $cityImages = City::query()->pluck('image', 'name_en');
        $today = now()->startOfDay();

        foreach ($this->events() as $event) {
            $startDate = $today->copy()->addDays($event['starts_in_days']);
            $endDate = $startDate->copy()->addDays($event['duration_days']);
            $city = $event['city'];

            unset($event['city'], $event['starts_in_days'], $event['duration_days']);

            Event::query()->updateOrCreate(
                ['name' => $event['name']],
                [
                    ...$event,
                    'city_id' => $cityIds[$city],
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'image' => $cityImages[$city],
                ],
            );
        }
    }

    /**
     * The events included in the MVP catalogue.
     *
     * @return array<int, array<string, mixed>>
     */
    private function events(): array
    {
        return [
            [
                'city' => 'Riyadh',
                'name' => 'موسم الرياض',
                'description' => 'أكبر موسم ترفيهي في المملكة: حفلات ومطاعم عالمية ومناطق تجارب في أنحاء الرياض.',
                'url' => 'https://riyadhseason.sa',
                'starts_in_days' => 3,
                'duration_days' => 45,
            ],
            [
                'city' => 'Jeddah',
                'name' => 'موسم جدة',
                'description' => 'فعاليات بحرية وترفيهية على واجهة جدة البحرية مع حفلات وعروض عائلية.',
                'url' => 'https://www.visitsaudi.com/ar',
                'starts_in_days' => 10,
                'duration_days' => 40,
            ],
            [
                'city' => 'AlUla',
                'name' => 'شتاء طنطورة',
                'description' => 'مهرجان يحيي تراث العلا بالموسيقى والحفلات في مرايا والبلدة القديمة.',
                'url' => 'https://www.experiencealula.com',
                'starts_in_days' => 5,
                'duration_days' => 21,
            ],
            [
                'city' => 'Abha',
                'name' => 'ليالي أبها',
                'description' => 'أمسيات موسيقية وثقافية على قمم عسير مع أجواء الضباب والجبال.',
                'url' => 'https://www.visitsaudi.com/ar',
                'starts_in_days' => 14,
                'duration_days' => 10,
            ],
            [
                'city' => 'Taif',
                'name' => 'سوق عكاظ',
                'description' => 'سوق تاريخي للشعر والأدب والحرف اليدوية في موقعه الأصلي بالطائف.',
                'url' => 'https://www.visitsaudi.com/ar',
                'starts_in_days' => 20,
                'duration_days' => 14,
            ],
            [
                'city' => 'Khobar',
                'name' => 'مهرجان الكورنيش بالخبر',
                'description' => 'أسواق وأركان طعام وأنشطة عائلية على كورنيش الخبر المطل على الخليج.',
                'url' => null,
                'starts_in_days' => 6,
                'duration_days' => 10,
            ],
            [
                'city' => 'Jazan',
                'name' => 'مهرجان جازان للقهوة',
                'description' => 'احتفال سنوي بالبن الجازاني والقهوة السعودية مع تجارب تذوق وفنون.',
                'url' => null,
                'starts_in_days' => 2,
                'duration_days' => 7,
            ],
            [
                'city' => 'Makkah',
                'name' => 'موسم مكة الثقافي',
                'description' => 'برنامج ثقافي وتراثي يحتفي بتاريخ مكة المكرمة وعاداتها وأسواقها القديمة.',
                'url' => 'https://www.visitsaudi.com/ar',
                'starts_in_days' => 12,
                'duration_days' => 12,
            ],
            [
                'city' => 'Madinah',
                'name' => 'مهرجان المدينة المنورة للتمور',
                'description' => 'سوق موسمي لأجود أصناف التمور المحلية مع فعاليات عائلية وثقافية.',
                'url' => 'https://www.visitsaudi.com/ar',
                'starts_in_days' => 18,
                'duration_days' => 9,
            ],
            [
                'city' => 'Riyadh',
                'name' => 'مهرجان الملك عبدالعزيز للإبل',
                'description' => 'مهرجان تراثي للمزاين والإبل مع سباقات وسوق شعبي وأمسيات شعبية.',
                'url' => 'https://www.visitsaudi.com/ar',
                'starts_in_days' => 25,
                'duration_days' => 14,
            ],
        ];
    }
}
