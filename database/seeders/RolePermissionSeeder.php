<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat Permissions (Hak Akses Spesifik)
        $permissions = [
            'manage users',
            'manage schools',
            'create exams',
            'edit exams',
            'delete exams',
            'take exams',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Buat Roles & Berikan Hak Akses
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all()); // Admin bisa semuanya

        $guru = Role::firstOrCreate(['name' => 'guru']);
        $guru->givePermissionTo(['create exams', 'edit exams', 'delete exams', 'view reports']);

        $siswa = Role::firstOrCreate(['name' => 'siswa']);
        $siswa->givePermissionTo(['take exams']);
    }
}
