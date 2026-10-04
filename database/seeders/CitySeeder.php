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
                'image' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/9/98/Riyadh_Skyline_showing_the_King_Abdullah_Financial_District_%28KAFD%29_and_the_famous_Kingdom_Tower_.jpg/1280px-Riyadh_Skyline_showing_the_King_Abdullah_Financial_District_%28KAFD%29_and_the_famous_Kingdom_Tower_.jpg',
            ],
            [
                'name' => 'جدة',
                'name_en' => 'Jeddah',
                'region' => 'منطقة مكة المكرمة',
                'latitude' => 21.4858,
                'longitude' => 39.1925,
                'description' => 'عروس البحر الأحمر وبوابتها، ومدينة تاريخية ساحلية.',
                'image' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/5/53/Jeddah_Corniche_12.jpg/1280px-Jeddah_Corniche_12.jpg',
            ],
            [
                'name' => 'العلا',
                'name_en' => 'AlUla',
                'region' => 'منطقة المدينة المنورة',
                'latitude' => 26.6084,
                'longitude' => 37.9231,
                'description' => 'متحف مفتوح للمقابر النبطية والمناظر الصحراوية.',
                'image' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/d/d6/Al-%27Ula_oasis_%281%29.jpg/1280px-Al-%27Ula_oasis_%281%29.jpg',
            ],
            [
                'name' => 'أبها',
                'name_en' => 'Abha',
                'region' => 'منطقة عسير',
                'latitude' => 18.2164,
                'longitude' => 42.5053,
                'description' => 'مدينة الضباب والجبال الخضراء في الجنوب.',
                'image' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/9/9f/ABHA_%286348527614%29.jpg/1280px-ABHA_%286348527614%29.jpg',
            ],
            [
                'name' => 'الطائف',
                'name_en' => 'Taif',
                'region' => 'منطقة مكة المكرمة',
                'latitude' => 21.2703,
                'longitude' => 40.4158,
                'description' => 'مدينة الورد والمصايف الباردة على المرتفعات.',
                'image' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/2/2d/Taif_Mountains_3.jpg/1280px-Taif_Mountains_3.jpg',
            ],
            [
                'name' => 'الخبر',
                'name_en' => 'Khobar',
                'region' => 'المنطقة الشرقية',
                'latitude' => 26.2794,
                'longitude' => 50.208,
                'description' => 'واجهة الخليج العربي وكورنيشها الشهير.',
                'image' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/7/7b/Al_Khobar_Corniche_%2812482233074%29.jpg/1280px-Al_Khobar_Corniche_%2812482233074%29.jpg',
            ],
            [
                'name' => 'مكة المكرمة',
                'name_en' => 'Makkah',
                'region' => 'منطقة مكة المكرمة',
                'latitude' => 21.3891,
                'longitude' => 39.8579,
                'description' => 'أقدس المدن الإسلامية وقبلة المسلمين.',
                'image' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/c/cb/Masjidul-HaramAerialView_%28cropped%29.jpg/1280px-Masjidul-HaramAerialView_%28cropped%29.jpg',
            ],
            [
                'name' => 'المدينة المنورة',
                'name_en' => 'Madinah',
                'region' => 'منطقة المدينة المنورة',
                'latitude' => 24.5247,
                'longitude' => 39.5692,
                'description' => 'مدينة النبي ﷺ وثاني أقدس المدن.',
                'image' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/e/e6/Masjid-Al-Nabawi_Madinah.jpg/1280px-Masjid-Al-Nabawi_Madinah.jpg',
            ],
            [
                'name' => 'جازان',
                'name_en' => 'Jazan',
                'region' => 'منطقة جازان',
                'latitude' => 16.8892,
                'longitude' => 42.5511,
                'description' => 'ساحل الجنوب وجزر فرسان وضباب الجبال.',
                'image' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/b/b2/Jazan_-_al-harth.jpg/1280px-Jazan_-_al-harth.jpg',
            ],
        ];
    }
}
