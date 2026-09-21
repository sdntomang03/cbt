<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Classroom;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $isTeacher = $user->hasRole('guru');
        $schoolId = $user->school_id;

        // 1. BUAT QUERY DASAR YANG DINAMIS
        // Jika yang login BUKAN Admin, paksa query untuk hanya mencari di sekolahnya sendiri
        $userQuery = User::query()->when(! $isAdmin, function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        });

        // 2. HITUNG STATISTIK MENGGUNAKAN QUERY DINAMIS TADI
        // Kita gunakan (clone) agar $userQuery bisa dipakai berulang kali tanpa tumpang tindih
        $examQuery = Exam::query()->when(! $isAdmin, function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })->when($isTeacher, function ($query) use ($user) {
            $query->where('teacher_id', $user->id);
        });

        $sessionQuery = ExamSession::query()->whereHas('exam', function ($query) use ($isAdmin, $schoolId, $isTeacher, $user) {
            $query->when(! $isAdmin, fn ($q) => $q->where('school_id', $schoolId))
                ->when($isTeacher, fn ($q) => $q->where('teacher_id', $user->id));
        });
        $classroomQuery = Classroom::query()->when(! $isAdmin, function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })->when($isTeacher, function ($query) use ($user) {
            $query->where('user_id', $user->id);
        });

        $stats = [
            'total_siswa' => $isTeacher
                ? (clone $classroomQuery)->withCount('students')->get()->sum('students_count')
                : (clone $userQuery)->role('siswa')->count(),
            'total_guru' => (clone $userQuery)->role('guru')->count(),

            // Jika Admin, hitung jumlah admin. Jika bukan, hitung jumlah operator di sekolahnya
            'total_staff' => $isAdmin
                ? User::role('admin')->count()
                : ($isTeacher ? (clone $classroomQuery)->count() : (clone $userQuery)->role('operator')->count()),
            'total_kelas' => (clone $classroomQuery)->count(),

            // Data global yang hanya masuk akal dilihat oleh Admin
            'total_sekolah' => $isAdmin ? School::count() : null,
            'total_roles' => $isAdmin ? Role::count() : null,
            'total_permissions' => $isAdmin ? Permission::count() : null,
            'total_ujian' => (clone $examQuery)->count(),
            'total_sesi' => (clone $sessionQuery)->count(),
            'sesi_aktif' => (clone $sessionQuery)->where('start_time', '<=', now())
                ->where('end_time', '>=', now())->count(),
            'peserta_selesai' => (clone $sessionQuery)->withCount([
                'students as completed_count' => fn ($query) => $query->where('exam_attempts.status', 'completed'),
            ])->get()->sum('completed_count'),
        ];

        $recentSessions = (clone $sessionQuery)
            ->with('exam:id,title')
            ->withCount('students')
            ->orderByDesc('start_time')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'user', 'isAdmin', 'isTeacher', 'recentSessions'));
    }
}
