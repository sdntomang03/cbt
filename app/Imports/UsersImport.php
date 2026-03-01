<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Opsional: Abaikan jika nama kosong
        if (! isset($row['nama']) || ! isset($row['username'])) {
            return null;
        }

        // Cek apakah user sudah ada (berdasarkan username atau email)
        $existingUser = User::where('username', $row['username'])->first();
        if ($existingUser) {
            return null; // Lewati jika sudah ada agar tidak error duplicate
        }

        return new User([
            'name' => $row['nama'],
            'username' => $row['username'], // Misal NISN untuk siswa
            'email' => $row['email'] ?? null,
            // Jika kolom password di excel kosong, set default ke '12345678'
            'password' => Hash::make($row['password'] ?? '12345678'),
            'role' => $row['role'] ?? 'siswa', // Default sebagai siswa
        ]);
    }
}
