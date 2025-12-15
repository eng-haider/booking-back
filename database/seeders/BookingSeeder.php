<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Venue;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete old bookings
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Booking::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get required data
        $customers = Customer::all();
        $venues = Venue::all();
        $user = User::first();
        
        // Get statuses
        $pendingStatus = Status::where('name', 'pending')->orWhere('name', 'قيد الانتظار')->first();
        $confirmedStatus = Status::where('name', 'confirmed')->orWhere('name', 'مؤكد')->first();
        $completedStatus = Status::where('name', 'completed')->orWhere('name', 'مكتمل')->first();

        // If no statuses exist, create them
        if (!$pendingStatus) {
            $pendingStatus = Status::create([
                'name' => 'قيد الانتظار',
                'slug' => 'pending',
                'type' => 'booking',
                'color' => '#FFA500',
                'is_active' => true,
            ]);
        }

        if (!$confirmedStatus) {
            $confirmedStatus = Status::create([
                'name' => 'مؤكد',
                'slug' => 'confirmed',
                'type' => 'booking',
                'color' => '#4CAF50',
                'is_active' => true,
            ]);
        }

        if (!$completedStatus) {
            $completedStatus = Status::create([
                'name' => 'مكتمل',
                'slug' => 'completed',
                'type' => 'booking',
                'color' => '#2196F3',
                'is_active' => true,
            ]);
        }

        if ($customers->isEmpty() || $venues->isEmpty()) {
            $this->command->warn('لا يوجد عملاء أو قاعات في قاعدة البيانات. يرجى تشغيل CustomerSeeder و VenueSeeder أولاً.');
            return;
        }

        $bookings = [
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->addDays(15),
                'start_time' => '10:00:00',
                'end_time' => '18:00:00',
                'status' => $confirmedStatus,
                'notes' => 'حجز يوم كامل للعائلة - 6 أشخاص',
                'special_requests' => 'نرغب بشاليه قريب من المسبح، وجبة غداء عائلية',
                'confirmed_at' => now(),
            ],
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->addDays(10),
                'start_time' => '09:00:00',
                'end_time' => '20:00:00',
                'status' => $confirmedStatus,
                'notes' => 'احتفال عيد ميلاد - 15 شخص',
                'special_requests' => 'كيك عيد ميلاد، ديكور بالونات، منطقة ألعاب للأطفال',
                'confirmed_at' => now(),
            ],
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->addDays(25),
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'status' => $pendingStatus,
                'notes' => 'رحلة عائلية - عطلة نهاية الأسبوع',
                'special_requests' => 'نحتاج شواية للحديقة، مسبح خاص للأطفال',
                'confirmed_at' => null,
            ],
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->addDays(5),
                'start_time' => '12:00:00',
                'end_time' => '22:00:00',
                'status' => $confirmedStatus,
                'notes' => 'جلسة استجمام للزوجين',
                'special_requests' => 'جلسة سبا ومساج، غرفة بإطلالة رومانسية',
                'confirmed_at' => now(),
            ],
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->subDays(5),
                'start_time' => '09:00:00',
                'end_time' => '19:00:00',
                'status' => $completedStatus,
                'notes' => 'نزهة عائلية - تم بنجاح',
                'special_requests' => null,
                'confirmed_at' => Carbon::now()->subDays(20),
                'completed_at' => Carbon::now()->subDays(5),
            ],
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->addDays(30),
                'start_time' => '10:00:00',
                'end_time' => '16:00:00',
                'status' => $pendingStatus,
                'notes' => 'تجمع عائلي كبير - 25 شخص',
                'special_requests' => 'منطقة مفتوحة للشواء، كراسي وطاولات إضافية',
                'confirmed_at' => null,
            ],
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->addDays(20),
                'start_time' => '08:00:00',
                'end_time' => '20:00:00',
                'status' => $confirmedStatus,
                'notes' => 'حفل تخرج - 30 شخص',
                'special_requests' => 'قاعة مغلقة مع إمكانية العرض، نظام صوت قوي',
                'confirmed_at' => now(),
            ],
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->addDays(12),
                'start_time' => '11:00:00',
                'end_time' => '18:00:00',
                'status' => $confirmedStatus,
                'notes' => 'يوم ترفيهي للأطفال',
                'special_requests' => 'ألعاب مائية، مسبح أطفال، وجبة خفيفة',
                'confirmed_at' => now(),
            ],
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->subDays(10),
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'status' => $completedStatus,
                'notes' => 'رحلة مدرسية - تم بنجاح',
                'special_requests' => null,
                'confirmed_at' => Carbon::now()->subDays(30),
                'completed_at' => Carbon::now()->subDays(10),
            ],
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->addDays(35),
                'start_time' => '13:00:00',
                'end_time' => '21:00:00',
                'status' => $pendingStatus,
                'notes' => 'جلسة يوغا وتأمل جماعية',
                'special_requests' => 'منطقة هادئة في الحديقة، موسيقى هادئة',
                'confirmed_at' => null,
            ],
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->addDays(18),
                'start_time' => '10:00:00',
                'end_time' => '19:00:00',
                'status' => $confirmedStatus,
                'notes' => 'ذكرى زواج - احتفال خاص',
                'special_requests' => 'ديكور رومانسي، عشاء فاخر، موسيقى حية',
                'confirmed_at' => now(),
            ],
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->addDays(8),
                'start_time' => '12:00:00',
                'end_time' => '20:00:00',
                'status' => $confirmedStatus,
                'notes' => 'إفطار رمضاني جماعي',
                'special_requests' => 'إعداد مائدة إفطار تقليدية، مكان للصلاة',
                'confirmed_at' => now(),
            ],
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->subDays(15),
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'status' => $completedStatus,
                'notes' => 'نزهة شبابية - تم بنجاح',
                'special_requests' => null,
                'confirmed_at' => Carbon::now()->subDays(40),
                'completed_at' => Carbon::now()->subDays(15),
            ],
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->addDays(22),
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'status' => $confirmedStatus,
                'notes' => 'معسكر صيفي للأطفال - يوم واحد',
                'special_requests' => 'أنشطة رياضية، مشرفين للأطفال، وجبات صحية',
                'confirmed_at' => now(),
            ],
            [
                'customer' => $customers->random(),
                'venue' => $venues->random(),
                'booking_date' => Carbon::now()->addDays(40),
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'status' => $pendingStatus,
                'notes' => 'حفل خطوبة في المنتجع',
                'special_requests' => 'منطقة خاصة للاحتفال، ديكور أنيق، خدمة ضيافة',
                'confirmed_at' => null,
            ],
        ];

        foreach ($bookings as $bookingData) {
            $booking = Booking::create([
                'user_id' => $user ? $user->id : null,
                'customer_id' => $bookingData['customer']->id,
                'venue_id' => $bookingData['venue']->id,
                'booking_reference' => 'BK-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'booking_date' => $bookingData['booking_date'],
                'start_time' => $bookingData['start_time'],
                'end_time' => $bookingData['end_time'],
                'total_price' => $bookingData['venue']->base_price,
                'status_id' => $bookingData['status']->id,
                'notes' => $bookingData['notes'],
                'special_requests' => $bookingData['special_requests'],
                'confirmed_at' => $bookingData['confirmed_at'],
                'completed_at' => $bookingData['completed_at'] ?? null,
            ]);
        }

        $this->command->info('تم إنشاء ' . count($bookings) . ' حجز بنجاح!');
    }
}
