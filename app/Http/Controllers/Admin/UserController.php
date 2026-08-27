<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Exports\UsersTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Tampilkan daftar User (Siswa/Admin/Guru)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = User::with(['school', 'roles']);

        if ($user->hasRole('admin')) {
            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }
        } else {
            $query->where('school_id', $user->school_id)
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'admin');
                });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();
        $schools = $user->hasRole('admin') ? School::orderBy('name')->get() : [];

        // AMBIL DAFTAR ROLE UNTUK DROPDOWN AJAX
        $roles = Role::pluck('name');

        return view('admin.users.index', compact('users', 'schools', 'roles'));
    }

    /**
     * Tampilkan form tambah user
     */
    public function create()
    {
        $schools = auth()->user()->hasRole('admin') ? School::orderBy('name')->get() : [];

        return view('admin.users.create', compact('schools'));
    }

    /**
     * Simpan data user baru ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
            // Validasi school_id hanya wajib jika yang login adalah super admin
            'school_id' => auth()->user()->hasRole('admin') ? 'required|exists:schools,id' : 'nullable',
        ]);

        $userData = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        // Jika super admin, masukkan school_id dari dropdown
        // Jika bukan super admin, Trait BelongsToSchool akan otomatis mengisinya
        if (auth()->user()->hasRole('admin')) {
            $userData['school_id'] = $request->school_id;
        }

        $user = User::create($userData);
        $user->assignRole($request->role);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Tampilkan form edit user
     */
    public function edit(User $user)
    {
        $schools = auth()->user()->hasRole('admin') ? School::orderBy('name')->get() : [];

        return view('admin.users.edit', compact('user', 'schools'));
    }

    /**
     * Update data user di database
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$user->id,
            'email' => 'nullable|email|unique:users,email,'.$user->id,
            'role' => 'required|in:admin,guru,siswa,operator',
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'school_id' => auth()->user()->hasRole('admin') ? $request->school_id : $user->school_id,
            'email_verified_at' => now(),
        ];

        // Jika password diisi, berarti ingin ganti password. Jika kosong, biarkan password lama.
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Data User berhasil diperbarui!');
    }

    /**
     * Hapus data user
     */
    public function destroy(User $user)
    {
        // (Opsional) Cegah admin menghapus dirinya sendiri
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus!');
    }

    /**
     * Fungsi untuk Import Excel yang kita bahas sebelumnya
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new UsersImport, $request->file('file_excel'));

            return redirect()->back()->with('success', 'Data User dari Excel berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: '.$e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        // Ambil school_id dari user yang sedang login (aktif)
        $schoolId = Auth::user()->school_id;

        // Pastikan user memiliki school_id (mencegah error jika Super Admin yang login tapi tidak punya sekolah)
        if (! $schoolId) {
            return back()->with('error', 'Anda tidak terikat dengan sekolah manapun.');
        }

        // Download file Excel
        return Excel::download(new UsersTemplateExport($schoolId), 'format_import_user.xlsx');
    }

    /**
     * Menghapus banyak user sekaligus berdasarkan ID yang dipilih.
     */
    public function bulkDelete(Request $request)
    {
        // Validasi data yang dikirim
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        try {
            // (Opsional) Cegah user yang sedang login terhapus secara tidak sengaja
            $idsToDelete = array_diff($request->ids, [auth()->id()]);

            if (empty($idsToDelete)) {
                return response()->json([
                    'message' => 'Gagal: Anda tidak dapat menghapus akun Anda sendiri dari daftar.',
                ], 422);
            }

            // Eksekusi hapus
            $deletedCount = User::whereIn('id', $idsToDelete)->delete();

            return response()->json([
                'message' => $deletedCount.' data user berhasil dihapus.',
            ]);

        } catch (QueryException $e) {
            // Tangani error jika user masih berelasi dengan data lain (misal: nilai ujian)
            if ($e->getCode() == '23000') {
                return response()->json([
                    'message' => 'Gagal! Beberapa user tidak dapat dihapus karena masih terhubung dengan data lain.',
                ], 422);
            }

            return response()->json(['message' => 'Terjadi kesalahan pada server/database.'], 500);
        }
    }

    public function exportSelected(Request $request)
    {
        // Ambil parameter ids dari URL
        $idsString = $request->query('ids');

        if (empty($idsString)) {
            return back()->with('error', 'Tidak ada data user yang dipilih untuk didownload.');
        }

        // Pecah string "1,2,3" menjadi array [1, 2, 3]
        $ids = explode(',', $idsString);

        // Download menggunakan class Export (lihat langkah ke-3 di bawah)
        return Excel::download(new UsersExport($ids), 'Data_User_Terpilih.xlsx');
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        // Opsional: Cegah admin mengubah role dirinya sendiri agar tidak terkunci keluar
        if (auth()->id() === $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak dapat mengubah role akun sendiri.',
            ], 403);
        }

        $user->syncRoles([$request->role]);

        return response()->json([
            'status' => 'success',
            'message' => 'Role berhasil diperbarui',
        ]);
    }
}
