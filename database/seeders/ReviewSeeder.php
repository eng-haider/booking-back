<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get test users and venues
        $users = User::all();
        $venues = Venue::all();

        if ($users->isEmpty() || $venues->isEmpty()) {
            $this->command->info('No users or venues found. Please seed users and venues first.');
            return;
        }

        $reviews = [
            [
                'rating' => 5,
                'comment' => 'ملعب ممتاز وخدمة رائعة! المكان نظيف جداً والموظفين متعاونين. أنصح الجميع بزيارته.',
            ],
            [
                'rating' => 4,
                'comment' => 'تجربة جيدة بشكل عام. المرافق جيدة والموقع مناسب. سأعود مرة أخرى بإذن الله.',
            ],
            [
                'rating' => 5,
                'comment' => 'أفضل ملعب في المنطقة! الأرضية ممتازة والإضاءة جيدة جداً. الحجز سهل والأسعار معقولة.',
            ],
            [
                'rating' => 3,
                'comment' => 'المكان جيد لكن يحتاج لبعض التحسينات. الخزائن قديمة والحمامات تحتاج صيانة.',
            ],
            [
                'rating' => 5,
                'comment' => 'تجربة رائعة! المكان مجهز بأحدث المعدات وموقف السيارات واسع. الموظفين محترفين جداً.',
            ],
            [
                'rating' => 4,
                'comment' => 'ملعب نظيف ومنظم. التكييف ممتاز والإضاءة جيدة. الأسعار مناسبة للخدمات المقدمة.',
            ],
            [
                'rating' => 5,
                'comment' => 'مكان ممتاز للعائلات والأصدقاء. يوجد مقهى وأماكن جلوس مريحة. أنصح به بشدة!',
            ],
            [
                'rating' => 4,
                'comment' => 'خدمة جيدة وأسعار معقولة. المكان يحتاج المزيد من الخزائن لكن بشكل عام تجربة جيدة.',
            ],
            [
                'rating' => 5,
                'comment' => 'الملعب في موقع ممتاز وسهل الوصول إليه. المرافق نظيفة جداً والإدارة متعاونة.',
            ],
            [
                'rating' => 3,
                'comment' => 'المكان مقبول لكن يحتاج لتحسين الخدمات. الحمامات غير نظيفة بما يكفي.',
            ],
            [
                'rating' => 5,
                'comment' => 'ملعب احترافي بكل المقاييس! المعدات حديثة والموقع رائع. سأكرر الحجز بالتأكيد.',
            ],
            [
                'rating' => 4,
                'comment' => 'تجربة جيدة جداً. الموظفين ودودين والخدمة سريعة. المكان يستحق الزيارة.',
            ],
        ];

        $reviewIndex = 0;
        foreach ($venues as $venue) {
            // Add 2-3 random reviews per venue
            $reviewCount = rand(2, 3);
            
            for ($i = 0; $i < $reviewCount && $reviewIndex < count($reviews); $i++) {
                $user = $users->random();
                
                // Check if this user already reviewed this venue
                $exists = Review::where('user_id', $user->id)
                    ->where('venue_id', $venue->id)
                    ->exists();
                
                if (!$exists) {
                    Review::create([
                        'user_id' => $user->id,
                        'customer_id' => null, // Customer relationship not needed for reviews
                        'venue_id' => $venue->id,
                        'rating' => $reviews[$reviewIndex]['rating'],
                        'comment' => $reviews[$reviewIndex]['comment'],
                    ]);
                    
                    $reviewIndex++;
                }
            }
        }
    }
}
