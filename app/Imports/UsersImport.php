<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Vinkla\Hashids\Facades\Hashids;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $realSchoolId = null;

        if (! empty($row['school_id'])) {
            // Decode mengembalikan bentuk array, contoh: [1]
            $decoded = Hashids::decode($row['school_id']);

            // Jika hasil decode kosong (berarti user mencoba mengubah kode secara asal)
            if (empty($decoded)) {
                throw new \Exception('Gagal import: Kode Sekolah tidak valid atau telah dimanipulasi.');
            }

            // Ambil angka aslinya dari dalam array
            $realSchoolId = $decoded[0];
        }

        return new User([
            'name' => $row['nama'],
            'username' => $row['username'],
            'email' => $row['email'],
            'password' => bcrypt('password123'),
            'school_id' => $realSchoolId, // Masukkan ID asli (angka) ke database
        ]);
    }
}
