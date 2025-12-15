<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for web guard (default)
        $permissions = [
            // Venue Management
            'view venues',
            'create venues',
            'edit venues',
            'delete venues',
            'manage own venues',
            'search venues',
            'feature venues',
            
            // Booking Management
            'view bookings',
            'create bookings',
            'edit bookings',
            'delete bookings',
            'manage own bookings',
            'confirm bookings',
            'cancel bookings',
            'complete bookings',
            'search bookings',
            'check availability',
            
            // Customer Management
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            'manage customer status',
            'search customers',
            'verify customers',
            
            // Provider Management
            'view providers',
            'create providers',
            'edit providers',
            'delete providers',
            'manage provider status',
            'manage own provider',
            
            // Category Management
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'reorder categories',
            
            // Review Management
            'view reviews',
            'create reviews',
            'edit reviews',
            'delete reviews',
            'manage own reviews',
            
            // Payment Management
            'view payments',
            'process payments',
            'refund payments',
            
            // Statistics & Reports
            'view statistics',
            'view reports',
            'export data',
            
            // System Settings
            'manage settings',
            'manage roles',
            'manage permissions',
        ];

        // Create permissions for all guards
        $guards = ['web', 'admin', 'provider', 'customer'];
        foreach ($guards as $guard) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission, 'guard_name' => $guard]
                );
            }
        }

        // Create roles for web/admin guards
        $this->createWebRoles();
        
        // Create roles for admin guard
        $this->createAdminRoles();
        
        // Create roles for provider guard
        $this->createProviderRoles();
        
        // Create roles for customer guard
        $this->createCustomerRoles();
    }

    private function createWebRoles()
    {
        // Admin Role - Full access
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        // Owner Role - Provider permissions
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $ownerRole->syncPermissions([
            'view venues',
            'create venues',
            'edit venues',
            'delete venues',
            'manage own venues',
            'search venues',
            'view bookings',
            'edit bookings',
            'manage own bookings',
            'confirm bookings',
            'cancel bookings',
            'complete bookings',
            'search bookings',
            'check availability',
            'view customers',
            'manage own provider',
            'view reviews',
            'create reviews',
            'manage own reviews',
            'view statistics',
            'view payments',
        ]);

        // Provider/Owner Role (backward compatibility)
        $providerRole = Role::firstOrCreate(['name' => 'provider', 'guard_name' => 'web']);
        $providerRole->syncPermissions([
            'view venues',
            'create venues',
            'edit venues',
            'delete venues',
            'manage own venues',
            'search venues',
            'view bookings',
            'edit bookings',
            'manage own bookings',
            'confirm bookings',
            'cancel bookings',
            'complete bookings',
            'search bookings',
            'check availability',
            'view customers',
            'manage own provider',
            'view reviews',
            'create reviews',
            'manage own reviews',
            'view statistics',
            'view payments',
        ]);

        // User Role
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $userRole->syncPermissions([
            'view venues',
            'view bookings',
        ]);

        echo "✓ Web guard roles created\n";
    }

    private function createAdminRoles()
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'admin')->get());

        echo "✓ Admin guard roles created\n";
    }

    private function createProviderRoles()
    {
        // Owner Role - All provider permissions
        $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'provider']);
        $ownerRole->syncPermissions(Permission::where('guard_name', 'provider')->get());

        // Also create provider role for backward compatibility
        $providerRole = Role::firstOrCreate(['name' => 'provider', 'guard_name' => 'provider']);
        $providerRole->syncPermissions(Permission::where('guard_name', 'provider')->get());

        echo "✓ Provider guard roles created\n";
    }

    private function createCustomerRoles()
    {
        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'customer']);
        $customerRole->syncPermissions(Permission::where('guard_name', 'customer')->whereIn('name', [
            'view venues',
            'view bookings',
            'create bookings',
            'manage own bookings',
            'cancel bookings',
            'create reviews',
            'edit reviews',
            'manage own reviews',
            'view payments',
        ])->get());

        echo "✓ Customer guard roles created\n";
    }
}
