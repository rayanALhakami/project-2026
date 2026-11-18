<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $images = [
                'National Museum' => 'saudi-national-museum',
                'Souq Al Zal' => 'souq-al-zal',
                'Najd Village' => 'najd-village',
                'Wadi Hanifah' => 'wadi-hanifah',
                'King Fahd Fountain' => 'king-fahd-fountain',
                'Silver Sands Beach' => 'al-saif-beach',
                'Al Hada Cable Car' => 'al-hada-cable-car',
                'Historic Jeddah' => 'historic-jeddah',
                'AlUla Old Town' => 'alula-old-town',
                'Al Soudah' => 'al-soudah',
                'Khobar Corniche' => 'khobar-corniche',
                'King Fahd Causeway' => 'king-fahd-causeway',
                'Jazan North Corniche' => 'jazan-north-corniche',
                'Farasan Islands' => 'farasan-islands',
            ];
            foreach ($images as $name => $image) {
                DB::table('places')->where('name_en', $name)->update(['image' => '/images/places/'.$image.'.jpg']);
            }
            DB::table('places')->where('name_en', 'Ottoman Castle')->update([
                'name' => 'قلعة الدوسرية',
                'name_en' => 'Al Dosariyah Castle',
                'image' => '/images/places/al-dosariyah-castle.jpg',
                'description' => 'قلعة تاريخية في وسط مدينة جازان فوق جبل يطل على المدينة ومينائها والبحر الأحمر، وتتميز بأبراجها الأسطوانية الأربعة وفنائها الداخلي.',
                'description_en' => 'A historic castle on a hill in central Jazan overlooking the city, its port and the Red Sea, with four cylindrical towers and an inner courtyard.',
            ]);
            $oldPark = DB::table('places')->where('name_en', 'New Abha Park')->first();
            if ($oldPark !== null && ! DB::table('places')->where('city_id', $oldPark->city_id)->where('name_en', 'Art Street')->exists()) {
                DB::table('places')->insert([
                    'city_id' => $oldPark->city_id,
                    'name' => 'شارع الفن',
                    'name_en' => 'Art Street',
                    'category' => 'landmark',
                    'description' => 'شارع للمشي والفنون في حي المفتاحة بأبها على طريق الملك خالد، يشتهر بأشجار الجاكرندا والفعاليات الفنية الموسمية.',
                    'description_en' => 'An arts and walking street on King Khalid Road in Al Muftaha, Abha, known for its jacaranda trees and seasonal art events.',
                    'latitude' => 18.21474,
                    'longitude' => 42.49361,
                    'image' => '/images/places/art-street-abha.jpg',
                    'ticket_price' => null,
                    'rating' => 0,
                    'tags' => json_encode(['فن', 'مشي', 'جاكرندا']),
                    'is_indoor' => false,
                    'family_friendly' => true,
                    'wheelchair_accessible' => false,
                    'prayer_facilities' => false,
                    'closed_friday' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('places')->whereIn('name_en', ['Al Baik', 'New Abha Park'])->delete();
        });
    }

    /**
     * Deleted catalogue entries and reviews cannot be reconstructed safely.
     */
    public function down(): void
    {
        //
    }
};
