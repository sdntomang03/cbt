<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Support\Facades\File;
// Sesuaikan dengan model Modul Anda
use PDO;

class OfflineDatabaseController extends Controller
{
    public function generate()
    {
        // 1. Tentukan lokasi penyimpanan sementara file SQLite
        $dbPath = storage_path('app/cbt_cache.db');

        // Hapus file lama jika masih ada
        if (File::exists($dbPath)) {
            File::delete($dbPath);
        }

        // Buat file kosong baru
        touch($dbPath);

        try {
            // 2. Buat koneksi langsung ke file SQLite baru menggunakan PDO
            $pdo = new PDO('sqlite:'.$dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 3. Buat Tabel yang persis sama dengan di Flutter
            $pdo->exec('
                CREATE TABLE offline_cache (
                    id TEXT PRIMARY KEY,
                    json_data TEXT,
                    updated_at TEXT
                )
            ');

            // Persiapkan statement insert
            $stmt = $pdo->prepare('INSERT INTO offline_cache (id, json_data, updated_at) VALUES (?, ?, ?)');
            $now = now()->toIso8601String();

            // =========================================================
            // 4. AMBIL DATA UJIAN (Replikasi JSON dari ApiPublicExamController)
            // =========================================================
            $publicExams = Exam::query()
                ->where('is_public', true)
                ->whereHas('examType', fn ($q) => $q->where('name', 'TKA'))
                ->with(['subject', 'level', 'examType'])
                // Opsional: Jika Anda ingin soal juga tersimpan di list ini,
                // tambahkan ->with('questions.options', 'questions.matches')
                ->latest()
                ->paginate(9); // Sesuaikan dengan logika API Anda

            $examsJson = json_encode([
                'success' => true,
                'data' => ['exams' => $publicExams],
            ]);

            // Masukkan JSON Ujian ke SQLite
            $stmt->execute(['exams_page_1', $examsJson, $now]);

            $pdo = null;

            // 6. Download file ke PC Admin, lalu otomatis hapus dari server agar tidak memenuhi storage
            return response()->download($dbPath, 'cbt_cache.db')->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal membuat database offline: '.$e->getMessage()]);
        }
    }
}
