<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersTemplateExport implements FromArray, ShouldAutoSize, WithHeadings
{
    protected $schoolId;

    // Menerima school_id dari controller saat class ini dipanggil
    public function __construct($schoolId)
    {
        $this->schoolId = $schoolId;
    }

    public function headings(): array
    {
        return [
            'nama',
            'username',
            'email',
            'password',
            'role',
            'school_id', // Tambahan kolom school_id
        ];
    }

    public function array(): array
    {
        // Memberikan contoh data baris pertama dan kedua
        return [
            [
                'Budi Santoso',
                '1234567890',
                'budi@sekolah.com',
                '12345678',
                'siswa',
                $this->schoolId, // Menggunakan ID sekolah user yang sedang login
                'COPY PASTE DATA ID SEKOLAH INI KE BARIS-BARIS SELANJUTNYA JIKA INGIN IMPORT KE SEKOLAH YANG SAMA',
            ],
            [
                'Siti Aminah',
                '0987654321',
                'siti@sekolah.com',
                '12345678',
                'guru',
                $this->schoolId,
            ],
        ];
    }
}
