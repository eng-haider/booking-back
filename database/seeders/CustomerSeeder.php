<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete old customers
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Customer::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $customers = [
            [
                'full_name' => 'أحمد محمد علي',
                'phone' => '+9647701234567',
                'email' => 'ahmed.mohammed@example.com',
                'password' => Hash::make('password123'),
                'address' => 'حي الجادرية، شارع الربيع',
                'city' => 'بغداد',
                'country' => 'العراق',
                'date_of_birth' => '1990-05-15',
                'gender' => 'male',
                'notes' => 'عميل مميز',
                'is_active' => true,
                'phone_verified_at' => now(),
            ],
            [
                'full_name' => 'فاطمة حسن الزهراء',
                'phone' => '+9647702345678',
                'email' => 'fatima.hassan@example.com',
                'password' => Hash::make('password123'),
                'address' => 'حي الكرادة، شارع الأمين',
                'city' => 'بغداد',
                'country' => 'العراق',
                'date_of_birth' => '1992-08-20',
                'gender' => 'female',
                'notes' => 'عميلة دائمة',
                'is_active' => true,
                'phone_verified_at' => now(),
            ],
            [
                'full_name' => 'علي حسين جاسم',
                'phone' => '+9647703456789',
                'email' => 'ali.hussein@example.com',
                'password' => Hash::make('password123'),
                'address' => 'حي المنصور، شارع الأميرات',
                'city' => 'بغداد',
                'country' => 'العراق',
                'date_of_birth' => '1988-03-10',
                'gender' => 'male',
                'notes' => null,
                'is_active' => true,
                'phone_verified_at' => now(),
            ],
            [
                'full_name' => 'زينب عبد الله',
                'phone' => '+9647704567890',
                'email' => 'zainab.abdullah@example.com',
                'password' => Hash::make('password123'),
                'address' => 'حي الحرية، شارع النصر',
                'city' => 'بغداد',
                'country' => 'العراق',
                'date_of_birth' => '1995-12-05',
                'gender' => 'female',
                'notes' => 'عميلة جديدة',
                'is_active' => true,
                'phone_verified_at' => now(),
            ],
            [
                'full_name' => 'محمود سعيد الربيعي',
                'phone' => '+9647705678901',
                'email' => 'mahmoud.saeed@example.com',
                'password' => Hash::make('password123'),
                'address' => 'حي اليرموك، شارع الخليج',
                'city' => 'بغداد',
                'country' => 'العراق',
                'date_of_birth' => '1985-07-22',
                'gender' => 'male',
                'notes' => 'عميل VIP',
                'is_active' => true,
                'phone_verified_at' => now(),
            ],
            [
                'full_name' => 'مريم خالد النعيمي',
                'phone' => '+9647706789012',
                'email' => 'mariam.khaled@example.com',
                'password' => Hash::make('password123'),
                'address' => 'حي الزيتون، شارع فلسطين',
                'city' => 'بغداد',
                'country' => 'العراق',
                'date_of_birth' => '1993-11-18',
                'gender' => 'female',
                'notes' => null,
                'is_active' => true,
                'phone_verified_at' => now(),
            ],
            [
                'full_name' => 'ياسر عدنان الكعبي',
                'phone' => '+9647707890123',
                'email' => 'yasser.adnan@example.com',
                'password' => Hash::make('password123'),
                'address' => 'حي الدورة، شارع العروبة',
                'city' => 'بغداد',
                'country' => 'العراق',
                'date_of_birth' => '1991-04-30',
                'gender' => 'male',
                'notes' => 'عميل منتظم',
                'is_active' => true,
                'phone_verified_at' => now(),
            ],
            [
                'full_name' => 'سارة أحمد الجبوري',
                'phone' => '+9647708901234',
                'email' => 'sara.ahmed@example.com',
                'password' => Hash::make('password123'),
                'address' => 'حي الشعب، شارع المثنى',
                'city' => 'بغداد',
                'country' => 'العراق',
                'date_of_birth' => '1994-09-25',
                'gender' => 'female',
                'notes' => null,
                'is_active' => true,
                'phone_verified_at' => now(),
            ],
            [
                'full_name' => 'حسام الدين طارق',
                'phone' => '+9647709012345',
                'email' => 'hussam.tariq@example.com',
                'password' => Hash::make('password123'),
                'address' => 'حي الأعظمية، شارع الرشيد',
                'city' => 'بغداد',
                'country' => 'العراق',
                'date_of_birth' => '1987-06-14',
                'gender' => 'male',
                'notes' => 'عميل مخلص',
                'is_active' => true,
                'phone_verified_at' => now(),
            ],
            [
                'full_name' => 'نور الهدى كريم',
                'phone' => '+9647700123456',
                'email' => 'noor.kareem@example.com',
                'password' => Hash::make('password123'),
                'address' => 'حي البياع، شارع الجامعة',
                'city' => 'بغداد',
                'country' => 'العراق',
                'date_of_birth' => '1996-02-08',
                'gender' => 'female',
                'notes' => 'عميلة نشطة',
                'is_active' => true,
                'phone_verified_at' => now(),
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        $this->command->info('تم إنشاء ' . count($customers) . ' عميل بنجاح!');
    }
}
