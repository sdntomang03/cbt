<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Vinkla\Hashids\Facades\Hashids;

// UBAH: Menggunakan ToCollection bukan ToModel
class UsersImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
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

            // 1. Simpan data user ke database secara langsung
            $user = User::create([
                'name' => $row['nama'],
                'username' => $row['username'],
                'email' => $row['email'],
                // Pastikan mengecek jika ada kolom password di Excel, jika tidak fallback ke password default
                'password' => ! empty($row['password']) ? bcrypt($row['password']) : bcrypt('password123'),
                'school_id' => $realSchoolId,
            ]);

            // 2. Ambil nama role dari kolom excel (ubah menjadi huruf kecil agar sesuai format Spatie)
            // Jika kolom role di excel kosong, otomatis jadikan 'siswa' sebagai default
            $roleName = ! empty($row['role']) ? strtolower(trim($row['role'])) : 'siswa';

            // 3. Pasang role ke user yang baru saja dibuat
            $user->assignRole($roleName);
        }
    }
}
