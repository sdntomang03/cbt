<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // 1. Bersihkan cache permission bawaan Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Daftar Lengkap Semua Permission di Aplikasi CBT Anda
        $permissions = [
            // Manajemen Inti (Hanya Super Admin)
            'manage schools',
            'manage roles',
            'manage permissions',

            // Manajemen Pengguna (Admin & Operator)
            'manage users',

            // Ujian Umum (General CBT)
            'view exams',
            'create exams',
            'edit exams',
            'delete exams',
            'take exams', // Khusus Siswa

            // Ujian Matematika Khusus (Math Exam)
            'view math exams',
            'create math exams',
            'edit math exams',
            'delete math exams',
            'take math exams', // Khusus Siswa

            // Laporan & Nilai
            'view reports',
            'export reports',
        ];

        // 3. Masukkan permissions ke database
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ========================================================
        // 4. PEMBUATAN ROLE & PEMBAGIAN HAK AKSES
        // ========================================================

        // --- ROLE: ADMIN ---
        // Mendapatkan SEMUA hak akses tanpa terkecuali
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // --- ROLE: OPERATOR ---
        // Fokus pada manajemen user sekolah dan pemantauan semua ujian di sekolahnya
        $operator = Role::firstOrCreate(['name' => 'operator']);
        $operator->syncPermissions([
            'manage users',
            'view exams', 'create exams', 'edit exams', 'delete exams',
            'view math exams', 'create math exams', 'edit math exams', 'delete math exams',
            'view reports', 'export reports',
        ]);

        // --- ROLE: GURU ---
        // Fokus pada pembuatan soal ujian dan melihat nilai (tidak bisa mengatur user/siswa)
        $guru = Role::firstOrCreate(['name' => 'guru']);
        $guru->syncPermissions([
            'view exams', 'create exams', 'edit exams', 'delete exams',
            'view math exams', 'create math exams', 'edit math exams', 'delete math exams',
            'view reports',
        ]);

        // --- ROLE: SISWA ---
        // Hanya bisa mengerjakan ujian
        $siswa = Role::firstOrCreate(['name' => 'siswa']);
        $siswa->syncPermissions([
            'take exams',
            'take math exams',
        ]);
    }
}
