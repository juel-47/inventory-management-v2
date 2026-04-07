<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure Spatie permissions are available
        $permissions = [
            'Manage Product Requests',
            'Create Product Requests',
            'View Product Stock',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $outletRole = Role::findOrCreate('Outlet User');
        $outletRole->syncPermissions(['Create Product Requests', 'View Product Stock']);

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(['Manage Product Requests', 'View Product Stock']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
