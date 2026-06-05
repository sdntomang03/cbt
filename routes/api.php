<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiPublicExamController;
use App\Http\Controllers\Api\ApiStudentExamController;
use Illuminate\Support\Facades\Route;

// =========================================================
// RUTE AUTH PUBLIK (Bisa diakses Flutter tanpa Login)
// =========================================================
Route::post('/login', [ApiAuthController::class, 'login']);

// =========================================================
// RUTE UJIAN PUBLIK (Tanpa Login Sanctum, via Session Token)
// =========================================================
Route::prefix('public/exams')->group(function () {
    // Info & Hasil Ujian
    Route::get('/', [ApiPublicExamController::class, 'index']);
    Route::get('/{slug}/detail', [ApiPublicExamController::class, 'detail']);
    Route::get('/{exam}/ranking', [ApiPublicExamController::class, 'ranking']);

    // PERBAIKAN DI SINI: Sesuaikan nama fungsinya!
    Route::get('/{exam}/verify', [ApiPublicExamController::class, 'getVerificationCode']);
    Route::post('/{exam}/verify', [ApiPublicExamController::class, 'verify']);

    // Alur Pengerjaan Ujian Publik
    Route::post('/{exam}/start', [ApiPublicExamController::class, 'start']);
    Route::post('/{exam}/answer', [ApiPublicExamController::class, 'storeAnswer']);
    Route::post('/{exam}/violation', [ApiPublicExamController::class, 'recordViolation']);
    Route::post('/{exam}/finish', [ApiPublicExamController::class, 'finish']);
    Route::post('/{exam}/restart', [ApiPublicExamController::class, 'restart']);
});

// =========================================================
// RUTE TERLINDUNGI (Siswa wajib kirim Bearer Token)
// =========================================================
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [ApiAuthController::class, 'logout']);

    // Area Ujian Internal Siswa
    Route::prefix('student')->group(function () {

        // -------------------------------------
        // 1. Ujian CBT Umum (Pilihan Ganda dll)
        // -------------------------------------
        Route::get('/exams', [ApiStudentExamController::class, 'index']);
        Route::post('/exams/{exam}/verify', [ApiStudentExamController::class, 'verifyToken']);
        Route::post('/exams/{exam}/start', [ApiStudentExamController::class, 'startExam']);
        Route::get('/exams/{exam}/question/{question_id}', [ApiStudentExamController::class, 'getQuestion']);
        Route::post('/exams/answer', [ApiStudentExamController::class, 'saveAnswer']);
        Route::post('/exams/violation', [ApiStudentExamController::class, 'recordViolation']);
        Route::get('/exams/{exam}/status', [ApiStudentExamController::class, 'checkStatus']);
        Route::post('/exams/{exam}/finish', [ApiStudentExamController::class, 'finishExam']);

        // -------------------------------------
        // 2. Ujian Matematika Khusus (Generator)
        // -------------------------------------
        Route::get('/math-exams', [ApiStudentExamController::class, 'mathIndex']);
        Route::post('/math-exams/{id}/start', [ApiStudentExamController::class, 'mathStart']);
        Route::post('/math-exams/{id}/answer', [ApiStudentExamController::class, 'mathSaveAnswer']);
        Route::post('/math-exams/{id}/finish', [ApiStudentExamController::class, 'mathFinish']);
        Route::get('/math-exams/{id}/result', [ApiStudentExamController::class, 'mathResult']);
    });

});
