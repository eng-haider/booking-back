<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            // Middle East
            [
                'name' => 'Iraq',
                'code' => 'IQ',
                'code_3' => 'IRQ',
                'phone_code' => '+964',
                'currency_code' => 'IQD',
                'currency_symbol' => 'ع.د',
                'is_active' => true,
            ],
            [
                'name' => 'Saudi Arabia',
                'code' => 'SA',
                'code_3' => 'SAU',
                'phone_code' => '+966',
                'currency_code' => 'SAR',
                'currency_symbol' => 'ر.س',
                'is_active' => true,
            ],
            [
                'name' => 'United Arab Emirates',
                'code' => 'AE',
                'code_3' => 'ARE',
                'phone_code' => '+971',
                'currency_code' => 'AED',
                'currency_symbol' => 'د.إ',
                'is_active' => true,
            ],
            [
                'name' => 'Kuwait',
                'code' => 'KW',
                'code_3' => 'KWT',
                'phone_code' => '+965',
                'currency_code' => 'KWD',
                'currency_symbol' => 'د.ك',
                'is_active' => true,
            ],
            [
                'name' => 'Qatar',
                'code' => 'QA',
                'code_3' => 'QAT',
                'phone_code' => '+974',
                'currency_code' => 'QAR',
                'currency_symbol' => 'ر.ق',
                'is_active' => true,
            ],
            [
                'name' => 'Bahrain',
                'code' => 'BH',
                'code_3' => 'BHR',
                'phone_code' => '+973',
                'currency_code' => 'BHD',
                'currency_symbol' => 'د.ب',
                'is_active' => true,
            ],
            [
                'name' => 'Oman',
                'code' => 'OM',
                'code_3' => 'OMN',
                'phone_code' => '+968',
                'currency_code' => 'OMR',
                'currency_symbol' => 'ر.ع.',
                'is_active' => true,
            ],
            [
                'name' => 'Jordan',
                'code' => 'JO',
                'code_3' => 'JOR',
                'phone_code' => '+962',
                'currency_code' => 'JOD',
                'currency_symbol' => 'د.ا',
                'is_active' => true,
            ],
            [
                'name' => 'Lebanon',
                'code' => 'LB',
                'code_3' => 'LBN',
                'phone_code' => '+961',
                'currency_code' => 'LBP',
                'currency_symbol' => 'ل.ل',
                'is_active' => true,
            ],
            [
                'name' => 'Egypt',
                'code' => 'EG',
                'code_3' => 'EGY',
                'phone_code' => '+20',
                'currency_code' => 'EGP',
                'currency_symbol' => 'ج.م',
                'is_active' => true,
            ],
            
            // Major International
            [
                'name' => 'United States',
                'code' => 'US',
                'code_3' => 'USA',
                'phone_code' => '+1',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'is_active' => true,
            ],
            [
                'name' => 'United Kingdom',
                'code' => 'GB',
                'code_3' => 'GBR',
                'phone_code' => '+44',
                'currency_code' => 'GBP',
                'currency_symbol' => '£',
                'is_active' => true,
            ],
            [
                'name' => 'Germany',
                'code' => 'DE',
                'code_3' => 'DEU',
                'phone_code' => '+49',
                'currency_code' => 'EUR',
                'currency_symbol' => '€',
                'is_active' => true,
            ],
            [
                'name' => 'France',
                'code' => 'FR',
                'code_3' => 'FRA',
                'phone_code' => '+33',
                'currency_code' => 'EUR',
                'currency_symbol' => '€',
                'is_active' => true,
            ],
            [
                'name' => 'Turkey',
                'code' => 'TR',
                'code_3' => 'TUR',
                'phone_code' => '+90',
                'currency_code' => 'TRY',
                'currency_symbol' => '₺',
                'is_active' => true,
            ],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}
