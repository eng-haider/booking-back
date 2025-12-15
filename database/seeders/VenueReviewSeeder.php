<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Venue;
use App\Models\Customer;
use App\Models\Booking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VenueReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete old reviews
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Review::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $venues = Venue::all();
        $customers = Customer::all();
        $users = \App\Models\User::all();

        if ($venues->isEmpty() || $customers->isEmpty()) {
            $this->command->warn('No venues or customers found. Please run seeders first.');
            return;
        }

        $arabicReviews = [
            [
                'rating' => 5,
                'comment' => 'منتجع رائع جداً! الخدمة ممتازة والموظفون متعاونون. المسابح نظيفة والطعام لذيذ. تجربة لا تنسى مع العائلة.',
            ],
            [
                'rating' => 5,
                'comment' => 'أفضل منتجع زرته في بغداد! كل شيء مرتب ونظيف. الشاليه واسع ومريح جداً. ننصح به بشدة للعوائل.',
            ],
            [
                'rating' => 4,
                'comment' => 'منتجع جميل ومرافق ممتازة، لكن الأسعار مرتفعة قليلاً. بشكل عام تجربة رائعة وسنعود مرة أخرى.',
            ],
            [
                'rating' => 5,
                'comment' => 'مكان هادئ ومريح للنفس. الحدائق جميلة والأطفال استمتعوا كثيراً بالملاعب والمسابح. خدمة من الدرجة الأولى!',
            ],
            [
                'rating' => 4,
                'comment' => 'منتجع نظيف ومرتب، الموقع ممتاز والإطلالة جميلة. الطعام جيد جداً. نقطة واحدة: الواي فاي بطيء قليلاً.',
            ],
            [
                'rating' => 5,
                'comment' => 'تجربة رائعة! المكان فخم والخدمات كاملة. السبا والساونا مريحين جداً. سنكرر الزيارة بالتأكيد.',
            ],
            [
                'rating' => 3,
                'comment' => 'المنتجع جيد ولكن يحتاج لبعض الصيانة. الموظفون لطيفون والمسبح نظيف. السعر مقبول.',
            ],
            [
                'rating' => 5,
                'comment' => 'خدمة VIP من الاستقبال حتى المغادرة! كل التفاصيل مدروسة بعناية. الشاليه فاخر والإطلالة ساحرة.',
            ],
            [
                'rating' => 4,
                'comment' => 'منتجع عائلي ممتاز، الأطفال استمتعوا بالألعاب المائية. الطعام متنوع ولذيذ. ننصح به للعائلات.',
            ],
            [
                'rating' => 5,
                'comment' => 'أجمل عطلة نهاية أسبوع! المكان نظيف جداً والموظفون محترمون. الحديقة واسعة ومناسبة للشواء.',
            ],
            [
                'rating' => 4,
                'comment' => 'منتجع جميل وهادئ، مناسب للاسترخاء. المسبح الخاص ميزة رائعة. السعر معقول مقارنة بالخدمات.',
            ],
            [
                'rating' => 5,
                'comment' => 'تجربة استثنائية! النظافة ممتازة والأمن والخصوصية متوفرة. قضينا وقتاً رائعاً مع الأصدقاء.',
            ],
            [
                'rating' => 3,
                'comment' => 'المكان جيد لكن كان مزدحماً قليلاً. الخدمة بطيئة في أوقات الذروة. بشكل عام تجربة مقبولة.',
            ],
            [
                'rating' => 5,
                'comment' => 'منتجع ريفي أصيل! أعاد لنا ذكريات الماضي الجميل. المزرعة والحيوانات أسعدت الأطفال كثيراً.',
            ],
            [
                'rating' => 4,
                'comment' => 'مكان ممتاز للاحتفالات العائلية. القاعات واسعة والديكور راقي. الطعام لذيذ والخدمة سريعة.',
            ],
            [
                'rating' => 5,
                'comment' => 'منتجع فاخر بكل المقاييس! الغرف نظيفة ومجهزة بكل شيء. المطعم يقدم أشهى الأطباق العراقية.',
            ],
            [
                'rating' => 4,
                'comment' => 'تجربة جميلة، الموقع قريب من المدينة والوصول سهل. المرافق نظيفة والموظفون ودودون.',
            ],
            [
                'rating' => 5,
                'comment' => 'أفضل منتجع للعوائل! الأنشطة متنوعة والأطفال لم يشعروا بالملل أبداً. ننصح به بقوة!',
            ],
            [
                'rating' => 3,
                'comment' => 'المنتجع جيد لكن الحجز كان صعباً. بعد الوصول كانت التجربة مرضية. يحتاج لتحسين نظام الحجز.',
            ],
            [
                'rating' => 5,
                'comment' => 'مكان رائع للهروب من صخب المدينة! الهدوء والطبيعة الخلابة. قضينا عطلة مثالية.',
            ],
        ];

        $createdReviews = 0;

        foreach ($venues as $venue) {
            // Create 2-4 reviews per venue
            $reviewCount = rand(2, 4);
            
            for ($i = 0; $i < $reviewCount; $i++) {
                $customer = $customers->random();
                $user = $users->random();
                $reviewData = $arabicReviews[array_rand($arabicReviews)];
                
                // Check if review already exists for this user and venue
                $exists = Review::where('user_id', $user->id)
                    ->where('venue_id', $venue->id)
                    ->exists();
                    
                if ($exists) {
                    continue; // Skip if already exists
                }
                
                Review::create([
                    'user_id' => $user->id,
                    'customer_id' => $customer->id,
                    'venue_id' => $venue->id,
                    'rating' => $reviewData['rating'],
                    'comment' => $reviewData['comment'],
                ]);
                
                $createdReviews++;
            }
        }

        $this->command->info("تم إنشاء {$createdReviews} تقييم للمنتجعات!");
    }
}
