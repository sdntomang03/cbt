<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiPublicExamController;
use App\Http\Controllers\Api\ApiStudentExamController;
use App\Http\Controllers\Api\StudentModuleController;
use App\Http\Controllers\Api\V1\Student\StudentAuthApiController;
use App\Http\Controllers\Api\V1\Student\StudentExamApiController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/student')->group(function () {
    Route::post('/login', [StudentAuthApiController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [StudentAuthApiController::class, 'logout']);
        Route::get('/profile', [StudentAuthApiController::class, 'profile']);
        Route::get('/dashboard', [StudentExamApiController::class, 'dashboard']);
        Route::get('/exams', [StudentExamApiController::class, 'index']);
        Route::get('/exams/{exam}', [StudentExamApiController::class, 'show']);
        Route::post('/exams/{exam}/start', [StudentExamApiController::class, 'start']);
        Route::get('/exams/{exam}/status', [StudentExamApiController::class, 'status']);
        Route::get('/attempts/{attempt}', [StudentExamApiController::class, 'attempt']);
        Route::get('/attempts/{attempt}/questions/{question}', [StudentExamApiController::class, 'question']);
        Route::post('/attempts/{attempt}/answers', [StudentExamApiController::class, 'answer']);
        Route::get('/attempts/{attempt}/progress', [StudentExamApiController::class, 'progress']);
        Route::post('/attempts/{attempt}/violation', [StudentExamApiController::class, 'violation']);
        Route::post('/attempts/{attempt}/submit', [StudentExamApiController::class, 'submit']);
        Route::get('/attempts/{attempt}/result', [StudentExamApiController::class, 'result']);
    });
});

// =========================================================
// RUTE AUTH PUBLIK (Bisa diakses Flutter tanpa Login)
// =========================================================
Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/register', [ApiAuthController::class, 'register']);
// =========================================================
// RUTE UJIAN PUBLIK (Tanpa Login Sanctum, via Session Token)
// =========================================================
Route::prefix('public/exams')->group(function () {
    // Info & Hasil Ujian
    Route::get('/', [ApiPublicExamController::class, 'index']);
    Route::get('/{slug}/detail', [ApiPublicExamController::class, 'detail']);
    Route::get('/{exam}/ranking', [ApiPublicExamController::class, 'getRanking']);
    Route::get('/ranking-nasional', [ApiPublicExamController::class, 'nationalRanking']);

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
    Route::post('/profile/update', [ApiAuthController::class, 'updateProfile']);
    Route::post('/profile/password', [ApiAuthController::class, 'updatePassword']);
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
    Route::get('/modules', [StudentModuleController::class, 'index']);

    Route::get('/modules/{slug}', [StudentModuleController::class, 'show']);
});

// Rute yang butuh Login (Dari Aplikasi)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout']);
    Route::get('/subscription/status', [SubscriptionController::class, 'status']);
    Route::post('/subscription/cancel/{orderId}', [SubscriptionController::class, 'cancelPending']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/student/history', [ApiPublicExamController::class, 'history']);
});

// Rute Webhook (TIDAK BOLEH dikunci Auth, karena dipanggil oleh Server Midtrans)
Route::post('/webhook/midtrans', [SubscriptionController::class, 'webhook']);
