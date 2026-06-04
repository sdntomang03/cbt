<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Ubah nama field penerima menjadi lebih umum (misal: login_id)
        // dan hapus aturan format '|email'
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required',
        ]);

        // 2. Cari user berdasarkan Email ATAU Username
        // Catatan: Ganti 'username' dengan nama kolom yang sesuai jika berbeda di database Anda
        $user = User::where('email', $request->login_id)
            ->orWhere('username', $request->login_id)
            ->first();

        // 3. Cek apakah user ada dan password cocok
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login_id' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        // Hapus token lama agar tidak menumpuk
        $user->tokens()->delete();

        // Buat token baru untuk Flutter
        $token = $user->createToken('flutter_mobile_app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login Berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    // Pastikan $user->role tidak membuat error 500 seperti dibahas sebelumnya
                    'role' => $user->roles->pluck('name')->first() ?? 'siswa',
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function logout(Request $request)
    {
        // Cabut token saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout Berhasil',
        ]);
    }
}
