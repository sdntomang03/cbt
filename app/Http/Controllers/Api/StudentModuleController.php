<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentModuleController extends Controller
{
    /**
     * Menampilkan daftar modul untuk siswa (Katalog)
     */
    public function index(Request $request)
    {
        $query = Module::where('status', 'published')
            ->where('is_public', true)
            ->with(['subject:id,name', 'level:id,name']); // Hanya ambil kolom yang perlu agar response lebih ringan

        // Filter berdasarkan Mata Pelajaran
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter berdasarkan Kelas/Level
        if ($request->filled('level_id')) {
            $query->where('level_id', $request->level_id);
        }

        // Filter Pencarian Judul
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $modules = $query->latest()->paginate(12)->withQueryString();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengambil data modul.',
            'data' => $modules,
        ], 200);
    }

    /**
     * Menampilkan detail modul beserta isinya
     */
    public function show($slug)
    {
        $module = Module::where('slug', $slug)
            ->where('status', 'published')
            ->with(['subject:id,name', 'level:id,name', 'author:id,name'])
            ->first();

        if (! $module) {
            return response()->json([
                'status' => 'error',
                'message' => 'Modul tidak ditemukan atau belum dipublikasikan.',
            ], 404);
        }

        // CEGATAN PREMIUM UNTUK API
        if ($module->is_premium) {
            // Kita gunakan auth('sanctum') karena ini biasanya standar untuk otentikasi API Laravel
            $user = auth('sanctum')->user();

            if (! $user || empty($user->premium_until) || Carbon::parse($user->premium_until)->isPast()) {
                return response()->json([
                    'status' => 'forbidden',
                    'message' => 'Akses ditolak. Modul ini eksklusif untuk member Premium yang aktif.',
                    'data' => [
                        'is_premium' => true,
                        'title' => $module->title,
                        'description' => $module->description,
                        'thumbnail' => $module->thumbnail ? asset('storage/'.$module->thumbnail) : null,
                    ],
                ], 403);
            }
        }

        // Tambah jumlah view
        $module->increment('view_count');

        // Format URL gambar dan PDF agar langsung bisa dipakai oleh Frontend (Android/React/Vue)
        $module->thumbnail_url = $module->thumbnail ? asset('storage/'.$module->thumbnail) : null;
        $module->document_url = $module->document_path ? asset('storage/'.$module->document_path) : null;

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengambil detail modul.',
            'data' => $module,
        ], 200);
    }
}
