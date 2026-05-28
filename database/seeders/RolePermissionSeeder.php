<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Menghapus cache permission sebelum menjalankan seeder (Penting!)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ---------------------------------------------------------
        // 1. DAFTAR PERMISSIONS BERDASARKAN FITUR/ROUTE
        // ---------------------------------------------------------
        $permissions = [

            // --- MANAJEMEN SISTEM INTI (Khusus Super Admin) ---
            'manage roles',
            'manage permissions',

            // --- MANAJEMEN SEKOLAH ---
            'view schools',
            'create schools',
            'edit schools',
            'delete schools',
            'export schools',
            'manage registration settings',

            // --- MANAJEMEN USERS (Guru, Operator, Siswa) ---
            'view users',
            'create users',
            'edit users',
            'delete users',
            'import users',
            'export users',

            // --- MANAJEMEN KELAS ---
            'view classrooms',
            'create classrooms',
            'edit classrooms',
            'delete classrooms',
            'manage classroom students',

            // --- MANAJEMEN UJIAN (CBT) ---
            'view exams',
            'create exams',
            'edit exams',
            'delete exams',
            'export exam grades',

            // --- MANAJEMEN SOAL (BANK SOAL) ---
            'view questions',
            'create questions',
            'edit questions',
            'delete questions',
            'import questions',

            // --- MANAJEMEN SESI UJIAN (JADWAL) ---
            'view exam sessions',
            'create exam sessions',
            'edit exam sessions',
            'delete exam sessions',
            'manage session students',
            'regenerate session token',

            // --- MANAJEMEN UJIAN MATEMATIKA KHUSUS ---
            'manage math exams', // Mencakup create, edit, delete, show
            'view math results',
            'export math results',
            'reset math exams',
            'print math worksheets',

            // --- PROCTORING (PENGAWASAN UJIAN) ---
            'monitor exams',
            'unlock student exam',
            'force finish student exam',
            'reset student session',

            // --- ANALISIS BUTIR SOAL ---
            'view item analysis',
            'export item analysis',

            // --- HAK AKSES SISWA ---
            'take exams',        // Mengerjakan ujian CBT Reguler
            'take math exams',   // Mengerjakan ujian Matematika
            'view own results',  // Melihat nilai sendiri
        ];

        // ---------------------------------------------------------
        // 2. SIMPAN PERMISSION KE DATABASE
        // ---------------------------------------------------------
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ---------------------------------------------------------
        // 3. PEMBUATAN ROLE & PEMBAGIAN HAK AKSES
        // ---------------------------------------------------------

        // A. ROLE: ADMIN (Super Administrator)
        // Memiliki semua akses tanpa terkecuali
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // B. ROLE: OPERATOR SEKOLAH
        // Mengelola data sekolah, siswa, dan jadwal, tapi tidak bisa hapus peran/permission
        $operatorRole = Role::firstOrCreate(['name' => 'operator']);
        $operatorRole->syncPermissions([
            'view schools', 'edit schools', 'manage registration settings',
            'view users', 'create users', 'edit users', 'delete users', 'import users', 'export users',
            'view classrooms', 'create classrooms', 'edit classrooms', 'delete classrooms', 'manage classroom students',
            'view exams', 'view exam sessions', 'create exam sessions', 'edit exam sessions', 'delete exam sessions', 'manage session students', 'regenerate session token',
            'monitor exams', 'unlock student exam', 'force finish student exam', 'reset student session',
            'view item analysis', 'export item analysis',
        ]);

        // C. ROLE: GURU (Teacher)
        // Fokus pada pembuatan soal, ujian, dan analisis
        $guruRole = Role::firstOrCreate(['name' => 'guru']);
        $guruRole->syncPermissions([
            'view users', // Untuk melihat daftar siswanya
            'view classrooms',
            'view exams', 'create exams', 'edit exams', 'delete exams', 'export exam grades',
            'view questions', 'create questions', 'edit questions', 'delete questions', 'import questions',
            'view exam sessions', 'create exam sessions', 'edit exam sessions', 'delete exam sessions', 'manage session students',
            'manage math exams', 'view math results', 'export math results', 'reset math exams', 'print math worksheets',
            'monitor exams', 'unlock student exam', 'force finish student exam',
            'view item analysis', 'export item analysis',
        ]);

        // D. ROLE: SISWA (Student)
        // Hanya untuk mengerjakan ujian dan melihat nilai
        $siswaRole = Role::firstOrCreate(['name' => 'siswa']);
        $siswaRole->syncPermissions([
            'take exams',
            'take math exams',
            'view own results',
        ]);
    }
}
