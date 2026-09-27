<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class StudentAuthApiController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'sekolah' => ['nullable', 'string', 'max:255'],
        ]);

        $username = $validated['username']
            ?: Str::before($validated['email'], '@').'-'.Str::lower(Str::random(6));

        $user = User::create([
            'name' => $validated['name'],
            'username' => $username,
            'email' => $validated['email'],
            'sekolah' => $validated['sekolah'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);
        $user->assignRole(Role::findOrCreate('siswa'));
        event(new Registered($user));

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran berhasil. Silakan aktifkan akun melalui tautan verifikasi yang dikirim ke email Anda.',
            'data' => ['email' => $user->email, 'email_verified' => false],
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $validated['username'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email belum diverifikasi. Silakan aktifkan akun melalui tautan yang dikirim ke email Anda.',
                'data' => ['email_verified' => false],
            ], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('student-mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'student' => $this->studentData($user),
            ],
        ]);
    }

    public function resendVerification(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'success' => true,
            'message' => 'Jika email terdaftar dan belum aktif, tautan verifikasi akan dikirim.',
            'data' => null,
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
            'data' => null,
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diambil.',
            'data' => $this->studentData($request->user()),
        ]);
    }

    private function studentData(User $user): array
    {
        $user->loadMissing([
            'school:id,name',
            'classrooms:id,name',
        ]);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'school_name' => $user->school?->name,
            'classroom_name' => $user->classrooms->first()?->name,
        ];
    }
}
