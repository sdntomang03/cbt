<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SchoolsExport;
use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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

    public function bulkDelete(Request $request)
    {
        // Validasi bahwa 'ids' harus berupa array
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:schools,id', // Pastikan ID benar-benar ada di database
        ]);

        // Hapus semua data yang ID-nya ada di dalam array
        $deletedCount = School::whereIn('id', $request->ids)->delete();

        // Kembalikan response JSON untuk dibaca oleh Axios
        return response()->json([
            'message' => $deletedCount.' data sekolah berhasil dihapus.',
        ]);
    }

    public function export(Request $request)
    {
        // 1. Ambil ID dari parameter URL (dikirim oleh frontend: ?ids=1,2,3)
        $idsString = $request->query('ids');

        // Jika tidak ada ID yang dipilih, kembalikan error atau redirect
        if (empty($idsString)) {
            return back()->with('error', 'Tidak ada data yang dipilih untuk didownload.');
        }

        // 2. Pecah string menjadi array ID
        $ids = explode(',', $idsString);

        // 3. Download menggunakan Maatwebsite Excel
        return Excel::download(new SchoolsExport($ids), 'Data_Sekolah_Terpilih.xlsx');
    }
}
