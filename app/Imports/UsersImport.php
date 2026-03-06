<?php

namespace App\Imports;

use App\Models\School;
use App\Models\User; // Jangan lupa import model School
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use function Symfony\Component\Clock\now;

class UsersImport implements ToCollection, WithHeadingRow
{
    /**
     * Menggunakan ToCollection agar kita bisa melakukan proses lanjutan
     * (seperti assignRole) setelah User berhasil di-create.
     */
    public function collection(Collection $rows)
    {
        // Ambil school_id default untuk multi-tenancy
        $schoolId = School::value('id') ?? 1;

        foreach ($rows as $row) {
            // Abaikan jika kolom nama atau username kosong
            if (empty($row['nama']) || empty($row['username'])) {
                continue;
            }

            // Cek apakah user sudah ada (berdasarkan username)
            $existingUser = User::where('username', $row['username'])->first();
            if ($existingUser) {
                continue; // Lewati baris ini jika user sudah ada
            }

            // 1. Simpan User ke database beserta school_id-nya
            $user = User::updateOrCreate(
                [
                    'username' => $row['username'], // Kunci pencarian (Harus Unik)
                ],
                [
                    'name' => $row['nama'],
                    'email' => $row['email'] ?? null,
                    // Jangan timpa password jika user sudah ada (kecuali diisi di excel)
                    'password' => isset($row['password']) ? Hash::make($row['password']) : Hash::make('12345678'),
                    'school_id' => $row['school_id'] ?? auth()->user()->school_id,
                    'email_verified_at' => now(),
                ]
            );

            // 2. Berikan Role menggunakan Spatie Permission
            $roleName = ! empty($row['role']) ? strtolower($row['role']) : 'siswa';
            $user->assignRole($roleName);
        }
    }
}
