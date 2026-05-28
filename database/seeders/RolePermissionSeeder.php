<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan cache permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ---------------------------------------------------------
        // 1. DAFTAR PERMISSIONS (Versi Ringkas / Bundling)
        // ---------------------------------------------------------
        $permissions = [
            'manage access',       // Mengelola role & permission
            'manage schools',      // CRUD & Export sekolah, setting registrasi
            'manage users',        // CRUD, Import, Export semua user
            'view users',          // Hanya melihat daftar user
            'manage classrooms',   // CRUD kelas & ploting siswa ke kelas
            'view classrooms',     // Hanya melihat daftar kelas
            'manage exams',        // CRUD ujian & export nilai
            'view exams',          // Hanya melihat daftar ujian
            'manage questions',    // CRUD & Import bank soal
            'manage exam sessions', // CRUD jadwal sesi, ploting peserta, generate token
            'manage math exams',   // CRUD ujian MTK, hasil, cetak LKS, reset
            'proctor exams',       // Monitor pengawasan, unlock, force finish
            'analyze exams',       // Lihat & export analisis butir soal
            'take exams',          // Akses mengerjakan ujian (Reguler & MTK)
            'view own results',    // Siswa melihat hasil/nilainya sendiri
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ---------------------------------------------------------
        // 2. PEMBAGIAN HAK AKSES KE MASING-MASING ROLE
        // ---------------------------------------------------------

        // A. ADMIN (Akses Penuh)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // B. OPERATOR (Fokus Administrasi Sekolah & Pengawasan)
        $operatorRole = Role::firstOrCreate(['name' => 'operator']);
        $operatorRole->syncPermissions([
            'manage schools',
            'manage users',
            'manage classrooms',
            'view exams',
            'manage exam sessions',
            'proctor exams',
            'analyze exams',
        ]);

        // C. GURU (Fokus Akademik, Soal & Ujian)
        $guruRole = Role::firstOrCreate(['name' => 'guru']);
        $guruRole->syncPermissions([
            'view users',
            'view classrooms',
            'manage exams',
            'manage questions',
            'manage exam sessions',
            'manage math exams',
            'proctor exams',
            'analyze exams',
        ]);

        // D. SISWA (Fokus Mengerjakan)
        $siswaRole = Role::firstOrCreate(['name' => 'siswa']);
        $siswaRole->syncPermissions([
            'take exams',
            'view own results',
        ]);
    }
}
