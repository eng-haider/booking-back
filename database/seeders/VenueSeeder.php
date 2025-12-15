<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\Venue;
use App\Models\User;
use App\Models\Category;
use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete old venues
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Venue::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get required data - Use the test provider account
        $owner = User::where('phone', '+9649876543210')->first();
        $provider = Provider::where('user_id', $owner?->id)->first();
        
        // If no provider exists, get any provider
        if (!$provider) {
            $provider = Provider::first();
        }
        
        $category = Category::first();
        $country = Country::where('name', 'Iraq')->orWhere('name', 'العراق')->first();

        // If no provider exists, create one
        if (!$provider) {
            $user = User::first();
            $provider = Provider::create([
                'user_id' => $user->id,
                'name' => 'مزود الخدمات الرئيسي',
                'slug' => 'main-provider',
                'description' => 'مزود خدمات موثوق ومعتمد',
                'email' => 'provider@booking.app',
                'phone' => '+9647700000000',
                'address' => 'بغداد، العراق',
                'license_number' => 'LIC-2024-001',
                'status' => 'active',
            ]);
        }

        // If no category exists, create one
        if (!$category) {
            $category = Category::create([
                'name' => 'منتجعات وشاليهات',
                'slug' => 'resorts',
                'description' => 'منتجعات سياحية وشاليهات للعوائل والاستجمام',
                'is_active' => true,
            ]);
        }

        // If no country exists, create one
        if (!$country) {
            $country = Country::create([
                'name' => 'العراق',
                'iso_code' => 'IQ',
                'phone_code' => '+964',
                'currency' => 'IQD',
            ]);
        }

        $venues = [
            [
                'provider_id' => $provider->id,
                'owner_id' => $owner ? $owner->id : null,
                'category_id' => $category->id,
                'name' => 'منتجع الواحة الخضراء',
                'description' => 'منتجع سياحي فاخر يقع على ضفاف نهر دجلة، يوفر أجواء هادئة ومريحة للعوائل. يحتوي على مسابح داخلية وخارجية، حدائق واسعة، ومطاعم متنوعة. مثالي للإقامة اليومية والعطلات العائلية.',
                'base_price' => 500000.00,
                'currency' => 'IQD',
                'status' => 'active',
                'buffer_minutes' => 60,
                'timezone' => 'Asia/Baghdad',
            ],
            [
                'provider_id' => $provider->id,
                'owner_id' => $owner ? $owner->id : null,
                'category_id' => $category->id,
                'name' => 'شاليهات النخيل الذهبية',
                'description' => 'شاليهات فاخرة للعوائل مع إطلالة بانورامية على البحيرة. كل شاليه يحتوي على مسبح خاص، شواية، ومنطقة جلوس خارجية. خدمة 24 ساعة وأمن متكامل.',
                'base_price' => 350000.00,
                'currency' => 'IQD',
                'status' => 'active',
                'buffer_minutes' => 60,
                'timezone' => 'Asia/Baghdad',
            ],
            [
                'provider_id' => $provider->id,
                'owner_id' => $owner ? $owner->id : null,
                'category_id' => $category->id,
                'name' => 'منتجع السعادة العائلي',
                'description' => 'منتجع ترفيهي متكامل للعوائل، يضم مدينة ألعاب مائية، مطاعم عائلية، وغرف فندقية فاخرة. يوفر برامج ترفيهية يومية للأطفال والكبار.',
                'base_price' => 450000.00,
                'currency' => 'IQD',
                'status' => 'active',
                'buffer_minutes' => 60,
                'timezone' => 'Asia/Baghdad',
            ],
            [
                'provider_id' => $provider->id,
                'owner_id' => $owner ? $owner->id : null,
                'category_id' => $category->id,
                'name' => 'منتجع الفردوس الصحي',
                'description' => 'منتجع صحي واستجمام مع سبا فاخر، ساونا، جاكوزي، ومركز للياقة البدنية. يوفر جلسات مساج وعلاجات طبيعية. مثالي للاسترخاء والراحة النفسية.',
                'base_price' => 600000.00,
                'currency' => 'IQD',
                'status' => 'active',
                'buffer_minutes' => 60,
                'timezone' => 'Asia/Baghdad',
            ],
            [
                'provider_id' => $provider->id,
                'owner_id' => $owner ? $owner->id : null,
                'category_id' => $category->id,
                'name' => 'شاليهات بحيرة الورد',
                'description' => 'شاليهات رومانسية على ضفاف بحيرة صناعية جميلة، محاطة بحدائق ورود طبيعية. توفر خصوصية تامة للعوائل مع خدمات الطعام والضيافة.',
                'base_price' => 400000.00,
                'currency' => 'IQD',
                'status' => 'active',
                'buffer_minutes' => 60,
                'timezone' => 'Asia/Baghdad',
            ],
            [
                'provider_id' => $provider->id,
                'owner_id' => $owner ? $owner->id : null,
                'category_id' => $category->id,
                'name' => 'منتجع الريف العراقي',
                'description' => 'منتجع ريفي تقليدي يعيد إحياء التراث العراقي الأصيل. يحتوي على بيوت طينية مُجددة، حدائق نخيل، ومزرعة حيوانات للأطفال. تجربة فريدة لمحبي الطبيعة.',
                'base_price' => 300000.00,
                'currency' => 'IQD',
                'status' => 'active',
                'buffer_minutes' => 60,
                'timezone' => 'Asia/Baghdad',
            ],
            [
                'provider_id' => $provider->id,
                'owner_id' => $owner ? $owner->id : null,
                'category_id' => $category->id,
                'name' => 'منتجع الأمواج المائي',
                'description' => 'منتجع ترفيهي مائي يضم أكبر مسبح أولمبي في بغداد، منزلقات مائية مثيرة، ومسابح للأطفال بأعماق مختلفة. يوفر دروس سباحة احترافية.',
                'base_price' => 550000.00,
                'currency' => 'IQD',
                'status' => 'active',
                'buffer_minutes' => 60,
                'timezone' => 'Asia/Baghdad',
            ],
            [
                'provider_id' => $provider->id,
                'owner_id' => $owner ? $owner->id : null,
                'category_id' => $category->id,
                'name' => 'شاليهات الجنة الهادئة',
                'description' => 'شاليهات فاخرة في موقع هادئ بعيداً عن صخب المدينة. كل شاليه مستقل بمسبح وحديقة خاصة. مثالي للعوائل الباحثة عن الخصوصية والهدوء.',
                'base_price' => 380000.00,
                'currency' => 'IQD',
                'status' => 'active',
                'buffer_minutes' => 60,
                'timezone' => 'Asia/Baghdad',
            ],
            [
                'provider_id' => $provider->id,
                'owner_id' => $owner ? $owner->id : null,
                'category_id' => $category->id,
                'name' => 'منتجع النجوم الخمس',
                'description' => 'منتجع سياحي من فئة خمس نجوم، يوفر غرفاً فاخرة، مطاعم عالمية، مسابح خارجية مدفأة، وخدمات كونسيرج متميزة. مناسب للمناسبات الخاصة والاحتفالات.',
                'base_price' => 750000.00,
                'currency' => 'IQD',
                'status' => 'active',
                'buffer_minutes' => 60,
                'timezone' => 'Asia/Baghdad',
            ],
            [
                'provider_id' => $provider->id,
                'owner_id' => $owner ? $owner->id : null,
                'category_id' => $category->id,
                'name' => 'منتجع المغامرات',
                'description' => 'منتجع رياضي ومغامرات يوفر أنشطة متنوعة: تسلق الجدران، حبال معلقة، رماية، وركوب الخيل. مناسب للشباب والعائلات المحبة للنشاطات الخارجية.',
                'base_price' => 320000.00,
                'currency' => 'IQD',
                'status' => 'active',
                'buffer_minutes' => 60,
                'timezone' => 'Asia/Baghdad',
            ],
        ];

        foreach ($venues as $venueData) {
            Venue::create($venueData);
        }

        $this->command->info('تم إنشاء ' . count($venues) . ' قاعة بنجاح!');
    }
}
