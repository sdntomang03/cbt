<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil data sekolah pertama (yang dibuat di RoleAndUserSeeder)
        $school = School::first();

        if (!$school) {
            $this->command->error('Data sekolah tidak ditemukan! Jalankan RoleAndUserSeeder terlebih dahulu.');
            return;
        }

        // 2. Buat Tahun Pelajaran
        $academicYear = DB::table('academic_years')->insertGetId([
            'school_id' => $school->id,
            'name'      => '2023/2024 Ganjil',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $academicYearGenap = DB::table('academic_years')->insertGetId([
            'school_id' => $school->id,
            'name'      => '2023/2024 Genap',
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Ambil data guru untuk dijadikan wali kelas
        // Asumsi kamu menggunakan Spatie Permission, kita ambil user dengan role 'guru'
        $guru = User::role('guru')->first();

        // 4. Buat Kelas
        $classAId = DB::table('classrooms')->insertGetId([
            'school_id'        => $school->id,
            'academic_year_id' => $academicYear,
            'user_id'          => $guru ? $guru->id : null, // Set wali kelas jika ada
            'name'             => 'XI RPL 1',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $classBId = DB::table('classrooms')->insertGetId([
            'school_id'        => $school->id,
            'academic_year_id' => $academicYear,
            'user_id'          => null, // Kelas B belum ada wali kelas
            'name'             => 'XI RPL 2',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // 5. Masukkan Siswa ke dalam Kelas
        // Ambil semua user yang memiliki role 'siswa'
        $students = User::role('siswa')->get();

        $classroomStudentData = [];

        // Kita bagi 2 kelas, setengah di XI RPL 1, setengah di XI RPL 2
        foreach ($students as $index => $student) {
            // Jika index genap masuk kelas A, ganjil masuk kelas B
            $classroomId = ($index % 2 == 0) ? $classAId : $classBId;

            $classroomStudentData[] = [
                'classroom_id' => $classroomId,
                'student_id'   => $student->id,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        // Insert massal ke tabel pivot classroom_student
        if (!empty($classroomStudentData)) {
            DB::table('classroom_student')->insert($classroomStudentData);
        }

        $this->command->info('Data Tahun Pelajaran, Kelas, dan Siswa berhasil di-seed!');
    }
}