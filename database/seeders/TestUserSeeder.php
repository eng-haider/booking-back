<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Provider;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Create Admin Test User
            $admin = User::firstOrCreate(
                ['phone' => '+9641234567890'],
                [
                    'name' => 'Admin Test User',
                    'email' => 'admin@test.com',
                    'role' => 'admin',
                    'password' => Hash::make('password'),
                ]
            );
            
            // Assign admin role for both web and admin guards
            if (!$admin->hasRole('admin', 'web')) {
                $admin->assignRole('admin'); // web guard (default)
            }
            if (!$admin->hasRole('admin', 'admin')) {
                $admin->assignRole(\Spatie\Permission\Models\Role::findByName('admin', 'admin'));
            }
            
            // Assign all admin permissions
            $adminPermissions = \Spatie\Permission\Models\Permission::where('guard_name', 'admin')->get();
            foreach ($adminPermissions as $permission) {
                if (!$admin->hasPermissionTo($permission, 'admin')) {
                    $admin->givePermissionTo($permission);
                }
            }
            
            echo "✓ Admin test user created: {$admin->phone} (OTP: 123456)\n";

            // Create Provider Test User
            $providerUser = User::firstOrCreate(
                ['phone' => '+9649876543210'],
                [
                    'name' => 'Provider Test User',
                    'email' => 'provider@test.com',
                    'role' => 'owner',
                    'password' => Hash::make('password'),
                ]
            );
            
            // Assign owner role for both web and provider guards
            if (!$providerUser->hasRole('owner', 'web')) {
                $providerUser->assignRole('owner'); // web guard (default)
            }
            if (!$providerUser->hasRole('owner', 'provider')) {
                $providerUser->assignRole(\Spatie\Permission\Models\Role::findByName('owner', 'provider'));
            }
            
            // Assign all provider permissions
            $providerPermissions = \Spatie\Permission\Models\Permission::where('guard_name', 'provider')->get();
            foreach ($providerPermissions as $permission) {
                if (!$providerUser->hasPermissionTo($permission, 'provider')) {
                    $providerUser->givePermissionTo($permission);
                }
            }

            // Create Provider Profile
            $governorate = \App\Models\Governorate::where('name_en', 'Baghdad')->first();
            
            Provider::firstOrCreate(
                ['user_id' => $providerUser->id],
                [
                    'governorate_id' => $governorate?->id ?? 1,
                    'name' => 'Test Sports Venue',
                    'slug' => 'test-sports-venue',
                    'description' => 'Test sports venue for development',
                    'email' => 'provider@test.com',
                    'phone' => '+9649876543210',
                    'address' => '123 Test Street, Al-Mansour',
                    'license_number' => 'TEST-LIC-001',
                    'status' => 'active',
                ]
            );
            echo "✓ Provider test user created: {$providerUser->phone} (OTP: 123456)\n";

            // Create Customer Test User (independent, no user_id)
            $customer = Customer::firstOrCreate(
                ['phone' => '+9645555555555'],
                [
                    'full_name' => 'Customer Test User',
                    'email' => 'customer@test.com',
                    'phone' => '+9645555555555',
                    'is_active' => true,
                ]
            );
            
            // Assign customer role
            if (!$customer->hasRole('customer')) {
                $customer->assignRole('customer');
            }
            
            // Assign all customer permissions
            $customerPermissions = \Spatie\Permission\Models\Permission::where('guard_name', 'customer')->get();
            foreach ($customerPermissions as $permission) {
                if (!$customer->hasPermissionTo($permission, 'customer')) {
                    $customer->givePermissionTo($permission);
                }
            }
            
            echo "✓ Customer test user created: {$customer->phone} (OTP: 123456)\n";

            echo "\n";
            echo "========================================\n";
            echo "Test Users Created Successfully!\n";
            echo "========================================\n";
            echo "Admin Phone:    +9641234567890\n";
            echo "Provider Phone: +9649876543210\n";
            echo "Customer Phone: +9645555555555\n";
            echo "All OTP Codes:  123456\n";
            echo "========================================\n";
        });
    }
}
