<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $schoolId = $user->school_id;

        // 1. BUAT QUERY DASAR YANG DINAMIS
        // Jika yang login BUKAN Admin, paksa query untuk hanya mencari di sekolahnya sendiri
        $userQuery = User::query()->when(! $isAdmin, function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        });

        // 2. HITUNG STATISTIK MENGGUNAKAN QUERY DINAMIS TADI
        // Kita gunakan (clone) agar $userQuery bisa dipakai berulang kali tanpa tumpang tindih
        $stats = [
            'total_siswa' => (clone $userQuery)->role('siswa')->count(),
            'total_guru' => (clone $userQuery)->role('guru')->count(),

            // Jika Admin, hitung jumlah admin. Jika bukan, hitung jumlah operator di sekolahnya
            'total_staff' => $isAdmin
                ? User::role('admin')->count()
                : (clone $userQuery)->role('operator')->count(),

            // Data global yang hanya masuk akal dilihat oleh Admin
            'total_sekolah' => $isAdmin ? School::count() : null,
            'total_roles' => $isAdmin ? Role::count() : null,
            'total_permissions' => $isAdmin ? Permission::count() : null,
        ];

        /* * OPSIONAL UNTUK UJIAN:
         * Jika Anda ingin menampilkan jumlah ujian, Anda bisa menerapkan logika yang sama.
         * * $examQuery = \App\Models\Exam::query()->when(!$isAdmin, function($q) use ($schoolId) {
         * $q->where('school_id', $schoolId);
         * });
         * $stats['total_ujian'] = $examQuery->count();
         */

        return view('admin.dashboard', compact('stats', 'user', 'isAdmin'));
    }
}
