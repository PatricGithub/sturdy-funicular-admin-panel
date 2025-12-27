<?php

namespace WebWizr\AdminPanel\Database\Seeders;

use WebWizr\AdminPanel\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = Permission::getDefaultPermissions();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'label' => $permission['label'],
                    'group' => $permission['group'],
                ]
            );
        }
    }
}
