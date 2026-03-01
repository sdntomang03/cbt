<?php

namespace Database\Seeders;

use App\Models\School;
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
        $school = School::create([
            'name' => 'Sekolah Pusat CBT',
            'domain' => 'pusat.cbt.com', // Sesuaikan jika ada kolom domain
        ]);
        // 2. Buat Role
        // Pastikan guard_name 'web' (default laravel) atau sesuaikan jika pakai API
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleGuru = Role::create(['name' => 'guru']);
        $roleSiswa = Role::create(['name' => 'siswa']);
        $roleOperator = Role::create(['name' => 'operator']);

        // 3. Buat User: ADMIN
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@cbt.com',
            'school_id' => $school->id, // Pastikan ada sekolah dengan ID 1 atau sesuaikan
            'username' => 'admin', // Tambahkan username untuk admin
            'password' => Hash::make('password'), // password default
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($roleAdmin);

        // 4. Buat User: GURU (Pak Budi)
        $guru = User::create([
            'name' => 'Pak Budi Santoso',
            'email' => 'guru@cbt.com',
            'school_id' => $school->id, // Pastikan ada sekolah dengan ID 1 atau sesuaikan
            'username' => 'guru', // Tambahkan username untuk guru
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $guru->assignRole($roleGuru);

        // 5. Buat User: SISWA (Andi)
        $operator = User::create([
            'name' => 'Andi Pratama',
            'email' => 'operator@cbt.com',
            'school_id' => $school->id, // Pastikan ada sekolah dengan ID 1 atau sesuaikan
            'username' => 'operator', // Tambahkan username untuk operator
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $operator->assignRole($roleOperator);

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
                'school_id' => $school->id, // Pastikan ada sekolah dengan ID 1 atau sesuaikan
                'email' => $faker->unique()->userName.'@cbt.com',
                'username' => 'siswa'.$i, // Username unik untuk setiap siswa (siswa1, siswa2, dst)
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            // Assign Role Siswa
            $dummySiswa->assignRole($roleSiswa);
        }
    }
}
