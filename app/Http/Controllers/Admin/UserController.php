<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Tampilkan daftar User (Siswa/Admin/Guru)
     */
    public function index(Request $request)
    {
        $query = User::with(['school', 'roles']);

        // Filter Dropdown: HANYA berlaku untuk Super Admin yang ingin melihat sekolah tertentu
        if (auth()->user()->hasRole('admin') && $request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Fitur Pencarian Teks
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        // Kirim data daftar sekolah ke layar (Hanya dikirim jika super_admin)
        $schools = auth()->user()->hasRole('admin') ? School::orderBy('name')->get() : [];

        return view('admin.users.index', compact('users', 'schools'));
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
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
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
            'role' => 'required|in:admin,teacher,student',
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'school_id' => auth()->user()->hasRole('admin') ? $request->school_id : $user->school_id,
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
}
