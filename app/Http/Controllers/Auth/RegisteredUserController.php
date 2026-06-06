<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\RegistrationSetting;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi Input (Termasuk Username)
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            'sekolah' => ['required', 'string', 'max:255'],
        ]);

        // 2. Ambil pengaturan pendaftaran pertama yang tersedia di database
        $setting = RegistrationSetting::first();

        // Proteksi jika admin belum mengatur sekolah pendaftaran
        if (! $setting) {
            return back()->withErrors(['email' => 'Sistem belum siap. Admin belum mengatur sekolah pendaftaran utama.']);
        }

        $schoolId = $setting->school_id;

        // 3. Buat User Siswa
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'school_id' => $schoolId,
            'sekolah' => $request->sekolah,
        ]);

        // Beri role 'siswa'
        $user->assignRole('siswa');

        // 4. OTOMATIS MASUKKAN KE BANYAK SESI UJIAN (Berdasarkan Checkbox di Setting)
        if (! empty($setting->default_exam_session_ids)) {

            $pivotData = [];
            // Kita looping array ID sesi yang ada di kolom JSON default_exam_session_ids
            foreach ($setting->default_exam_session_ids as $sessionId) {
                $pivotData[$sessionId] = [
                    'school_id' => $schoolId,
                    'status' => 'not_started',
                    'violation_count' => 0,
                    'is_locked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Simpan semua baris sekaligus ke tabel pivot 'exam_session_user'
            $user->examSessions()->attach($pivotData);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
