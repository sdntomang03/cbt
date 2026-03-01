<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $schools = School::orderBy('name', 'asc')->get();

        // Jika request datang dari proses AJAX (saat Alpine.js me-refresh tabel)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['schools' => $schools]);
        }

        // Tampilan pertama kali halaman diload
        return view('admin.schools.index', compact('schools'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:schools,name',
            'domain' => 'nullable|string|max:255',
        ]);

        School::create($request->only(['name', 'domain']));

        return response()->json(['success' => true, 'message' => 'Data sekolah berhasil ditambahkan!']);
    }

    public function update(Request $request, School $school)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:schools,name,'.$school->id,
            'domain' => 'nullable|string|max:255',
        ]);

        $school->update($request->only(['name', 'domain']));

        return response()->json(['success' => true, 'message' => 'Data sekolah berhasil diperbarui!']);
    }

    public function destroy(School $school)
    {
        $school->delete();

        return response()->json(['success' => true, 'message' => 'Sekolah berhasil dihapus!']);
    }
}
