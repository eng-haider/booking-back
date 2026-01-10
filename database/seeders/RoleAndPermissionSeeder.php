<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::beginTransaction();
        try {
            $config = config('rolesAndPermissions');
            $guardName = $config['guard_name'] ?? 'api';
            $roles = $config['roles'] ?? [];

            // Collect all unique permissions from all roles
            $allPermissions = [];
            foreach ($roles as $permissions) {
                foreach ($permissions as $permissionName) {
                    $allPermissions[$permissionName] = true;
                }
            }

            // Create all permissions with the single guard
            foreach (array_keys($allPermissions) as $permissionName) {
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => $guardName,
                ]);
            }

            // Create roles and assign permissions
            foreach ($roles as $roleName => $permissions) {
                $role = Role::firstOrCreate([
                    'name' => $roleName,
                    'guard_name' => $guardName,
                ]);

                // Get permission models for this role
                $permissionModels = Permission::where('guard_name', $guardName)
                    ->whereIn('name', $permissions)
                    ->get();

                // Sync permissions to the role
                $role->syncPermissions($permissionModels);

                echo "✓ Role '{$roleName}' created with " . count($permissions) . " permissions\n";
            }

            DB::commit();
            echo "✓ All roles and permissions seeded successfully with guard: {$guardName}\n";
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
