<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Support\Facades\File;
use PDO;
use ZipArchive;

class OfflineDatabaseController extends Controller
{
    public function generate()
    {
        // 1. Siapkan folder sementara untuk proses Export
        $exportId = uniqid('offline_');
        $tempFolder = storage_path('app/temp_export/'.$exportId);
        $imagesFolder = $tempFolder.'/images/offline';
        $dbPath = $tempFolder.'/cbt_cache.db';
        $zipPath = storage_path('app/cbt_offline_data.zip');

        // Buat struktur folder
        File::makeDirectory($imagesFolder, 0755, true, true);
        touch($dbPath);

        try {
            // 2. Buat koneksi SQLite
            $pdo = new PDO('sqlite:'.$dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec('
                CREATE TABLE offline_cache (
                    id TEXT PRIMARY KEY,
                    json_data TEXT,
                    updated_at TEXT
                )
            ');

            $stmt = $pdo->prepare('INSERT INTO offline_cache (id, json_data, updated_at) VALUES (?, ?, ?)');
            $now = now()->toIso8601String();

            // =========================================================
            // 3. FUNGSI HELPER UNTUK MENGAMBIL GAMBAR DARI JSON
            // =========================================================
            // Fungsi ini akan mencari link /storage/ di dalam JSON,
            // mengcopy gambar ke folder temporary, dan mengubah linknya ke asset: flutter
            $processImagesAndUrls = function ($dataArray) use ($imagesFolder) {
                // Encode ke JSON tanpa escape slash agar mudah di-regex
                $jsonString = json_encode($dataArray, JSON_UNESCAPED_SLASHES);

                // Cari string yang mengandung URL storage gambar, misal: "https://domain.com/storage/soal_images/abc.png"
                // atau "/storage/soal_images/abc.png"
                $jsonString = preg_replace_callback('/"(?:https?:\/\/[^"]+)?\/storage\/([^"]+)"/', function ($matches) use ($imagesFolder) {
                    $relativePath = $matches[1]; // misal: soal_images/abc.png
                    $serverPath = storage_path('app/public/'.$relativePath);
                    $filename = basename($relativePath);

                    // Jika gambar fisik ada di server Laravel, copy ke folder zip
                    if (File::exists($serverPath)) {
                        File::copy($serverPath, $imagesFolder.'/'.$filename);

                        // Kembalikan format URL asset untuk Flutter
                        return '"asset:assets/images/offline/'.$filename.'"';
                    }

                    // Jika gambar tidak ditemukan, biarkan seperti semula
                    return $matches[0];
                }, $jsonString);

                return $jsonString;
            };

            // =========================================================
            // 4. PROSES LIST UJIAN (HALAMAN DEPAN)
            // =========================================================
            $publicExams = Exam::query()
                ->where('is_public', true)
                ->whereHas('examType', fn ($q) => $q->where('name', 'TKA'))
                ->with(['subject', 'level', 'examType'])
                ->latest()
                ->paginate(9);

            $examsData = ['success' => true, 'data' => ['exams' => $publicExams]];
            $examsJson = $processImagesAndUrls($examsData);
            $stmt->execute(['exams_page_1', $examsJson, $now]);

            // =========================================================
            // 5. PROSES DETAIL SOAL (SAAT UJIAN DIMULAI)
            // =========================================================
            $allExams = Exam::where('is_public', true)->get();
            foreach ($allExams as $exam) {
                // Pastikan nama relasi 'questions' dan 'options' sesuai dengan Model Anda
                $examDetail = Exam::with([
                    'subject',
                    'level',
                    'examType',
                    'questions',
                    'questions.options',
                ])->find($exam->id);

                $detailData = ['success' => true, 'data' => $examDetail];
                $detailJson = $processImagesAndUrls($detailData); // Ekstrak & ubah URL gambar soal
                $stmt->execute(['exam_detail_'.$exam->id, $detailJson, $now]);
            }

            // =========================================================
            // 6. BUNGKUS KE DALAM FILE ZIP
            // =========================================================
            $pdo = null; // Tutup koneksi DB

            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                // Masukkan file database
                $zip->addFile($dbPath, 'cbt_cache.db');

                // Masukkan semua gambar yang berhasil di-copy
                $files = File::files($imagesFolder);
                foreach ($files as $file) {
                    $zip->addFile($file->getPathname(), 'images/offline/'.$file->getFilename());
                }
                $zip->close();
            }

            // Hapus folder sementara
            File::deleteDirectory($tempFolder);

            // 7. Download file ZIP ke PC Admin, hapus dari server setelah terkirim
            return response()->download($zipPath, 'CBT_Offline_Assets.zip')->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            // Bersihkan folder sementara jika terjadi error
            if (File::exists($tempFolder)) {
                File::deleteDirectory($tempFolder);
            }

            return response()->json(['error' => 'Gagal membuat database offline: '.$e->getMessage()]);
        }
    }
}
