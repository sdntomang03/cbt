<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Tambahkan ini di atas

class KawanBacaController extends Controller
{
    // Fungsi bantuan untuk mengambil data dari file JSON
    // Fungsi bantuan untuk mengambil data dari file JSON
    private function getBankBacaan()
    {
        // Menggunakan jalur mutlak / absolut (Anti-Gagal)
        $path = storage_path('app/bacaan.json');

        // 1. Cek apakah file benar-benar ada di Windows
        if (! file_exists($path)) {
            return [
                'kata' => ['File JSON Tidak Ditemukan!'],
                'kalimat' => ['Cek kembali folder storage/app/'],
                'paragraf' => ['Pastikan nama file adalah bacaan.json'],
                'bacaan' => [['judul' => 'Error', 'isi' => 'File tidak ada di '.$path]],
            ];
        }

        // 2. Baca isi filenya
        $json = file_get_contents($path);
        $data = json_decode($json, true);

        // 3. Cek apakah ada salah ketik tanda kutip/koma di dalam file JSON
        if ($data === null) {
            return [
                'kata' => ['Format JSON Rusak!'],
                'kalimat' => ['Ada tanda baca yang salah di dalam file bacaan.json'],
                'paragraf' => ['Gunakan validator JSON online untuk mengeceknya.'],
                'bacaan' => [['judul' => 'Error JSON', 'isi' => 'Format JSON tidak valid.']],
            ];
        }

        return $data;
    }

    public function index()
    {
        return view('kawan-baca.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'jenis_bacaan' => 'required|array|min:1',
            'jumlah' => 'required|integer|min:1|max:20',
        ]);

        $bankBacaan = $this->getBankBacaan(); // Panggil JSON di sini

        $jenisTerpilih = $request->jenis_bacaan;
        $jumlahLatihan = $request->jumlah;
        $materiLatihan = [];

        for ($i = 0; $i < $jumlahLatihan; $i++) {
            $kategoriAcak = $jenisTerpilih[array_rand($jenisTerpilih)];

            // Cek apakah data tersedia di kategori tersebut
            if (isset($bankBacaan[$kategoriAcak]) && count($bankBacaan[$kategoriAcak]) > 0) {
                $dataKategori = $bankBacaan[$kategoriAcak];
                $itemAcak = $dataKategori[array_rand($dataKategori)];

                $materiLatihan[] = [
                    'id' => $i + 1,
                    'tipe' => $kategoriAcak,
                    'konten' => $itemAcak,
                ];
            }
        }

        session(['materi_baca' => $materiLatihan]);

        return redirect()->route('baca.latihan');
    }

    public function latihan()
    {
        $materi = session('materi_baca');

        if (! $materi) {
            return redirect()->route('baca.index');
        }

        return view('kawan-baca.latihan', compact('materi'));
    }
}
