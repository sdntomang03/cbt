<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class ApiAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->login_id)
            ->orWhere('username', $request->login_id)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login_id' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        $user->tokens()->delete();

        $token = $user->createToken('flutter_mobile_app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login Berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->pluck('name')->first() ?? 'siswa',
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    // ==========================================
    // TAMBAHAN: FUNGSI REGISTER
    // ==========================================
    public function register(Request $request)
    {
        // 1. Validasi Input dari Flutter
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'asal_sekolah_public' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' akan otomatis mengecek 'password_confirmation'
        ]);

        // 2. Buat User Baru
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'asal_sekolah_public' => $request->asal_sekolah_public,
            'password' => Hash::make($request->password),
        ]);

        // Opsional: Berikan role 'siswa' secara default jika Anda menggunakan Spatie Permission
        if (class_exists(Role::class)) {
            $user->assignRole('siswa');
        }

        // 3. Buatkan Token (Agar langsung login)
        $token = $user->createToken('flutter_mobile_app')->plainTextToken;

        // 4. Kembalikan Response format JSON persis seperti fungsi Login
        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi Berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => 'siswa', // Default role
                ],
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201); // 201 adalah status code HTTP untuk "Created"
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout Berhasil',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user(); // Ambil user yang sedang login dari token Sanctum

        // Update data di database (Sesuaikan nama kolom dengan tabel users/students Anda)
        $user->nama_peserta = $request->nama_peserta;
        $user->asal_sekolah = $request->asal_sekolah;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        $user = $request->user();

        // Cek apakah password lama yang diketik cocok dengan di database
        if (! Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi lama tidak sesuai!',
            ], 400); // Bad Request
        }

        // Jika cocok, ubah ke password baru
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diubah',
        ]);
    }
}
