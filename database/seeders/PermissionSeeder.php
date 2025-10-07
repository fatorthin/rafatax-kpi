<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User permissions
            'user.viewAny', 'user.view', 'user.create', 'user.update', 'user.delete',
            // Role permissions
            'role.viewAny', 'role.view', 'role.create', 'role.update', 'role.delete',
            // LogBook permissions
            'logbook.viewAny', 'logbook.view', 'logbook.create', 'logbook.update', 'logbook.delete',
            // ClientReport permissions
            'clientreport.viewAny', 'clientreport.view', 'clientreport.create', 'clientreport.update', 'clientreport.delete', 'clientreport.verify',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::where('name', 'admin')->first();
        $staff = Role::where('name', 'staff')->first();

        if ($admin) {
            $admin->syncPermissions($permissions);
        }

        if ($staff) {
            $staff->syncPermissions([
                'user.viewAny', 'user.view',
                'role.viewAny', 'role.view',
                'logbook.viewAny', 'logbook.view', 'logbook.create', 'logbook.update',
                'clientreport.viewAny', 'clientreport.view', 'clientreport.create', 'clientreport.update',
            ]);
        }
    }
}


