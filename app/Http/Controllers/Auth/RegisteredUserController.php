<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
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
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username, // Gunakan username dari request
            'password' => Hash::make($request->password),
        ]);
        $user->assignRole('siswa');
        $firstSessionId = ExamSession::value('id');
        // Asumsi Anda mengambil school_id dari user yang sedang login atau dari form
        $schoolId = auth()->user()->school_id; // Atau sesuaikan dengan sumber data Anda

        // Menyisipkan data ke tabel pivot exam_session_user
        $user->examSessions()->attach($request->exam_session_id, [
            'school_id' => $schoolId,
        ]);
        // Jika ada minimal 1 sesi ujian di database, hubungkan user
        if ($firstSessionId) {
            $user->examSessions()->attach($firstSessionId);
        }
        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
