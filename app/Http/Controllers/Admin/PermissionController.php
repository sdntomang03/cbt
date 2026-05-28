<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Tampilkan daftar Permission
     */
    public function index(Request $request)
    {
        $query = Permission::query();

        // Fitur Pencarian
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Tampilkan dengan pagination
        $permissions = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Simpan Permission baru ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        // Simpan dan paksa menggunakan huruf kecil
        Permission::create(['name' => strtolower($request->name)]);

        return redirect()->back()->with('success', 'Hak Akses (Permission) baru berhasil ditambahkan!');
    }

    /**
     * Update nama Permission
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,'.$permission->id,
        ]);

        // Proteksi: Cegah perubahan nama pada permission inti sistem
        $corePermissions = [
            'manage users', 'manage schools', 'create exams',
            'edit exams', 'delete exams', 'take exams', 'view reports',
        ];

        if (in_array($permission->name, $corePermissions) && strtolower($request->name) !== $permission->name) {
            return redirect()->back()->with('error', 'Akses Ditolak: Permission inti bawaan sistem tidak boleh diubah namanya!');
        }

        $permission->update(['name' => strtolower($request->name)]);

        return redirect()->back()->with('success', 'Nama Permission berhasil diperbarui!');
    }

    /**
     * Hapus Permission
     */
    public function destroy(Permission $permission)
    {
        // Proteksi: Cegah penghapusan permission inti sistem
        $corePermissions = [
            'manage users', 'manage schools', 'create exams',
            'edit exams', 'delete exams', 'take exams', 'view reports',
        ];

        if (in_array($permission->name, $corePermissions)) {
            return redirect()->back()->with('error', 'Akses Ditolak: Permission inti bawaan sistem tidak boleh dihapus!');
        }

        $permission->delete();

        return redirect()->back()->with('success', 'Permission berhasil dihapus secara permanen!');
    }

    /**
     * Dapatkan daftar user yang memiliki permission ini (Via AJAX)
     */
    public function users(Permission $permission)
    {
        // Spatie otomatis mencari user yang punya permission ini secara langsung
        // MAUPUN dari Role (Jabatannya).
        $users = User::permission($permission->name)
            ->with('school') // Eager load relasi sekolah agar tidak lambat
            ->select('id', 'name', 'school_id', 'username')
            ->orderBy('name')
            ->get();

        // Rapikan data untuk dikirim ke Alpine.js
        $formattedUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'school' => $user->school->name ?? 'Pusat / Admin',
            ];
        });

        return response()->json([
            'permission_name' => $permission->name,
            'users' => $formattedUsers,
        ]);
    }
}
