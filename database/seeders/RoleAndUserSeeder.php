<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Reset Cached Roles & Permissions (Wajib untuk Spatie)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat Role
        // Pastikan guard_name 'web' (default laravel) atau sesuaikan jika pakai API
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleGuru = Role::create(['name' => 'guru']);
        $roleSiswa = Role::create(['name' => 'siswa']);

        // 3. Buat User: ADMIN
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@cbt.com',
            'password' => Hash::make('password'), // password default
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($roleAdmin);

        // 4. Buat User: GURU (Pak Budi)
        $guru = User::create([
            'name' => 'Pak Budi Santoso',
            'email' => 'guru@cbt.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $guru->assignRole($roleGuru);

        // 5. Buat User: SISWA (Andi)
        $siswa = User::create([
            'name' => 'Andi Pratama',
            'email' => 'siswa@cbt.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $siswa->assignRole($roleSiswa);

        // 6. Opsional: Buat 10 Siswa Dummy Tambahan untuk test Pagination
        $faker = Faker::create('id_ID');

        // 2. Loop pembuatan user dummy
        for ($i = 1; $i <= 10; $i++) {
            $dummySiswa = User::create([
                // Menghasilkan nama Indonesia acak (Contoh: Budi Santoso, Siti Aminah)
                'name' => $faker->name,

                // Opsional: Email tetap pakai pola angka agar mudah login saat testing
                // 'email' => 'siswa'.$i.'@cbt.com',

                // ATAU: Gunakan email random dari faker (unik)
                'email' => $faker->unique()->userName.'@cbt.com',

                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            // Assign Role Siswa
            $dummySiswa->assignRole($roleSiswa);
        }
    }
}
