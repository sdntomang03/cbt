<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    public function store(Request $request)
    {
        // SunEditor mengirim file dengan key 'file-0'
        if ($request->hasFile('file-0')) {
            $file = $request->file('file-0');

            // Validasi (Opsional tapi disarankan)
            $request->validate([
                'file-0' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            // Simpan ke folder 'public/uploads/soal'
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/soal', $filename, 'public');

            // Return format JSON sesuai standar SunEditor
            return response()->json([
                'result' => [
                    [
                        'url' => asset('storage/'.$path),
                        'name' => $filename,
                        'size' => $file->getSize(),
                    ],
                ],
            ]);
        }

        return response()->json(['error' => 'Gagal upload gambar'], 400);
    }
}
