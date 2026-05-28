<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Tampilkan daftar Role beserta Permission-nya
     */
    public function index()
    {
        // Ambil semua role beserta permission yang dimiliki
        $roles = Role::with('permissions')->orderBy('name')->get();

        // Ambil semua permission yang tersedia di database
        $permissions = Permission::orderBy('name')->get();

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Simpan Role baru & pasang Permission-nya
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Buat role baru (paksa huruf kecil agar seragam)
        $role = Role::create(['name' => strtolower($request->name)]);

        // Pasang permission jika ada yang dicentang
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->back()->with('success', 'Role baru berhasil ditambahkan!');
    }

    /**
     * Update nama Role & sinkronisasi Permission-nya
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,'.$role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Proteksi: Mencegah nama role bawaan sistem diubah
        $coreRoles = ['admin', 'guru', 'siswa', 'operator'];
        if (in_array($role->name, $coreRoles) && strtolower($request->name) !== $role->name) {
            return redirect()->back()->with('error', 'Peringatan: Nama role inti sistem tidak boleh diubah!');
        }

        // Update nama role
        $role->update(['name' => strtolower($request->name)]);

        // Sinkronisasi permission (Spatie otomatis menghapus yang tidak dicentang & menambah yang baru)
        $permissions = $request->permissions ?? [];
        $role->syncPermissions($permissions);

        return redirect()->back()->with('success', 'Hak akses untuk Role '.strtoupper($role->name).' berhasil diperbarui!');
    }

    /**
     * Hapus Role
     */
    public function destroy(Role $role)
    {
        // Proteksi: Mencegah role bawaan sistem dihapus
        $coreRoles = ['admin', 'guru', 'siswa', 'operator'];
        if (in_array($role->name, $coreRoles)) {
            return redirect()->back()->with('error', 'Peringatan: Role inti bawaan sistem tidak boleh dihapus!');
        }

        $role->delete();

        return redirect()->back()->with('success', 'Role berhasil dihapus!');
    }
}
