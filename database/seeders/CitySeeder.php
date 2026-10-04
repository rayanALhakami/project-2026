<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Seed the Saudi cities covered by the guide.
     */
    public function run(): void
    {
        foreach ($this->cities() as $city) {
            City::query()->updateOrCreate(
                ['name_en' => $city['name_en']],
                $city,
            );
        }
    }

    /**
     * The cities included in the MVP catalogue.
     *
     * @return array<int, array<string, mixed>>
     */
    private function cities(): array
    {
        return [
            [
                'name' => 'الرياض',
                'name_en' => 'Riyadh',
                'region' => 'منطقة الرياض',
                'latitude' => 24.7136,
                'longitude' => 46.6753,
                'description' => 'العاصمة الحديثة، مزيج من الأبراج والتراث النجدي.',
                'image' => null,
            ],
            [
                'name' => 'جدة',
                'name_en' => 'Jeddah',
                'region' => 'منطقة مكة المكرمة',
                'latitude' => 21.4858,
                'longitude' => 39.1925,
                'description' => 'عروس البحر الأحمر وبوابتها، ومدينة تاريخية ساحلية.',
                'image' => null,
            ],
            [
                'name' => 'العلا',
                'name_en' => 'AlUla',
                'region' => 'منطقة المدينة المنورة',
                'latitude' => 26.6084,
                'longitude' => 37.9231,
                'description' => 'متحف مفتوح للمقابر النبطية والمناظر الصحراوية.',
                'image' => null,
            ],
            [
                'name' => 'أبها',
                'name_en' => 'Abha',
                'region' => 'منطقة عسير',
                'latitude' => 18.2164,
                'longitude' => 42.5053,
                'description' => 'مدينة الضباب والجبال الخضراء في الجنوب.',
                'image' => null,
            ],
            [
                'name' => 'الطائف',
                'name_en' => 'Taif',
                'region' => 'منطقة مكة المكرمة',
                'latitude' => 21.2703,
                'longitude' => 40.4158,
                'description' => 'مدينة الورد والمصايف الباردة على المرتفعات.',
                'image' => null,
            ],
            [
                'name' => 'الخبر',
                'name_en' => 'Khobar',
                'region' => 'المنطقة الشرقية',
                'latitude' => 26.2794,
                'longitude' => 50.208,
                'description' => 'واجهة الخليج العربي وكورنيشها الشهير.',
                'image' => null,
            ],
            [
                'name' => 'مكة المكرمة',
                'name_en' => 'Makkah',
                'region' => 'منطقة مكة المكرمة',
                'latitude' => 21.3891,
                'longitude' => 39.8579,
                'description' => 'أقدس المدن الإسلامية وقبلة المسلمين.',
                'image' => null,
            ],
            [
                'name' => 'المدينة المنورة',
                'name_en' => 'Madinah',
                'region' => 'منطقة المدينة المنورة',
                'latitude' => 24.5247,
                'longitude' => 39.5692,
                'description' => 'مدينة النبي ﷺ وثاني أقدس المدن.',
                'image' => null,
            ],
            [
                'name' => 'جازان',
                'name_en' => 'Jazan',
                'region' => 'منطقة جازان',
                'latitude' => 16.8892,
                'longitude' => 42.5511,
                'description' => 'ساحل الجنوب وجزر فرسان وضباب الجبال.',
                'image' => null,
            ],
        ];
    }
}
