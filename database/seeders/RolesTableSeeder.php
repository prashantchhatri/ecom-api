<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        $roles = ['super_admin', 'company_admin', 'user', 'seller'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['role' => $role]);
        }
    }
}

