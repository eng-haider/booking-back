<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * @deprecated Use RoleAndPermissionSeeder instead
 * This seeder is kept for backward compatibility but now delegates to RoleAndPermissionSeeder
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delegate to the new seeder that uses config/rolesAndPermissions.php
        $this->call(RoleAndPermissionSeeder::class);
    }
}
