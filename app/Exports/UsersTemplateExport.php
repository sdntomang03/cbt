<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Vinkla\Hashids\Facades\Hashids; // Tambahkan facade Hashids di sini

class UsersTemplateExport implements FromArray, ShouldAutoSize, WithHeadings
{
    protected $schoolId;

    // Menerima school_id dari controller saat class ini dipanggil
    public function __construct($schoolId)
    {
        $this->schoolId = $schoolId;[cite: 3]
    }

    public function headings(): array
    {
        return [
            'nama',[cite: 3]
            'username',[cite: 3]
            'email',[cite: 3]
            'password',[cite: 3]
            'role',[cite: 3]
            'school_id', // Tambahan kolom school_id
        ];
    }

    public function array(): array
    {
        // Ubah ID sekolah asli menjadi karakter acak (contoh: "jR3qM")
        $hashedSchoolId = Hashids::encode($this->schoolId);

        // Memberikan contoh data baris pertama dan kedua
        return [
            [
                'Budi Santoso',[cite: 3]
                '1234567890',[cite: 3]
                'budi@sekolah.com',[cite: 3]
                '12345678',[cite: 3]
                'siswa',[cite: 3]
                $hashedSchoolId, // Menggunakan ID sekolah yang sudah di-encode
                'COPY PASTE DATA KODE SEKOLAH INI KE BARIS-BARIS SELANJUTNYA JIKA INGIN IMPORT KE SEKOLAH YANG SAMA', // Penyesuaian teks panduan[cite: 3]
            ],
            [
                'Siti Aminah',[cite: 3]
                '0987654321',[cite: 3]
                'siti@sekolah.com',[cite: 3]
                '12345678',[cite: 3]
                'guru',[cite: 3]
                $hashedSchoolId, // Menggunakan ID sekolah yang sudah di-encode
            ],
        ];
    }
}
