<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking admin users and their permissions...\n\n";

// Find all admin users
$admins = App\Models\User::where('role', 'admin')->get();

if ($admins->isEmpty()) {
    echo "❌ No admin users found!\n";
    exit(1);
}

foreach ($admins as $admin) {
    echo "Admin User: {$admin->email} (ID: {$admin->id})\n";
    echo "User role field: {$admin->role}\n";
    
    // Check if user has admin role for admin guard
    $hasAdminRole = $admin->hasRole('admin', 'admin');
    echo "Has 'admin' role (admin guard): " . ($hasAdminRole ? 'Yes ✓' : 'No ✗') . "\n";
    
    // Get all roles for admin guard
    $roles = $admin->getRoleNames('admin');
    echo "Roles (admin guard): " . ($roles->isEmpty() ? 'None' : $roles->implode(', ')) . "\n";
    
    // Assign admin role if not present
    if (!$hasAdminRole) {
        echo "Assigning 'admin' role to user...\n";
        $admin->assignRole('admin');
        echo "✓ Admin role assigned!\n";
    }
    
    // Check permissions
    $permissions = $admin->getPermissionsViaRoles('admin');
    echo "Total permissions (admin guard): {$permissions->count()}\n";
    
    // Check specific permission
    $hasCreateProviders = $admin->hasPermissionTo('create providers', 'admin');
    echo "Has 'create providers' permission: " . ($hasCreateProviders ? 'Yes ✓' : 'No ✗') . "\n";
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

echo "Done!\n";
