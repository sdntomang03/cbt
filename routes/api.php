<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiStudentExamController;
use Illuminate\Support\Facades\Route;

// =========================================================
// RUTE PUBLIK (Bisa diakses Flutter tanpa Login)
// =========================================================
Route::post('/login', [ApiAuthController::class, 'login']);

// =========================================================
// RUTE TERLINDUNGI (Flutter wajib kirim Bearer Token)
// =========================================================
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [ApiAuthController::class, 'logout']);

    // Ujian Umum
    Route::prefix('student')->group(function () {
        Route::get('/exams', [ApiStudentExamController::class, 'index']);
        Route::post('/exams/{exam}/verify', [ApiStudentExamController::class, 'verifyToken']);
        Route::post('/exams/{exam}/start', [ApiStudentExamController::class, 'startExam']);
        Route::get('/exams/{exam}/question/{question_id}', [ApiStudentExamController::class, 'getQuestion']);
        Route::post('/exams/answer', [ApiStudentExamController::class, 'saveAnswer']);
        Route::post('/exams/violation', [ApiStudentExamController::class, 'recordViolation']);
        Route::post('/exams/{exam}/finish', [ApiStudentExamController::class, 'finishExam']);

        // Ujian Matematika
        Route::get('/math-exams', [ApiStudentExamController::class, 'mathIndex']);
        Route::post('/math-exams/{id}/start', [ApiStudentExamController::class, 'mathStart']);
        Route::post('/math-exams/{id}/answer', [ApiStudentExamController::class, 'mathSaveAnswer']);
        Route::post('/math-exams/{id}/finish', [ApiStudentExamController::class, 'mathFinish']);
    });
});
