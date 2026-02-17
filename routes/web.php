<?php

use App\Http\Controllers\Admin\ExamSessionController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionAjaxController;
use App\Http\Controllers\Student\StudentExamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// --- GROUP PROFILE (Bawaan Breeze/Jetstream) ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- GROUP ADMIN & GURU ---
Route::middleware(['auth', 'role:admin|guru'])
    ->prefix('admin')
    ->name('admin.') // Prefix nama route jadi 'admin.exams.index', dll
    ->group(function () {

        // 1. Manajemen Ujian (Bank Soal)
        Route::resource('exams', ExamController::class);

        // 2. Manajemen Soal (AJAX)
        Route::get('/exams/{exam}/questions', [QuestionAjaxController::class, 'index'])->name('ajax.questions.index');
        Route::post('/exams/{exam}/questions', [QuestionAjaxController::class, 'store'])->name('ajax.questions.store');
        Route::put('/questions/{question}', [QuestionAjaxController::class, 'update'])->name('ajax.questions.update');
        Route::delete('/questions/{question}', [QuestionAjaxController::class, 'destroy'])->name('ajax.questions.destroy');

        // 3. Upload Gambar (Untuk Summernote/CKEditor)
        Route::post('/upload-image', [ImageUploadController::class, 'store'])->name('image.upload');

        // 4. Manajemen Sesi Ujian (Jadwal) - INI YANG BARU
        Route::resource('exam-sessions', ExamSessionController::class);
        Route::post('exam-sessions/{exam_session}/regenerate-token', [ExamSessionController::class, 'regenerateToken'])
            ->name('exam-sessions.regenerate-token');

        Route::get('exam-sessions/{exam_session}/students', [ExamSessionController::class, 'studentIndex'])->name('exam-sessions.students');
        Route::post('exam-sessions/{exam_session}/students', [ExamSessionController::class, 'studentStore'])->name('exam-sessions.students.store');
        Route::delete('exam-sessions/{exam_session}/students/{user}', [ExamSessionController::class, 'studentDestroy'])->name('exam-sessions.students.destroy');

    });

// --- GROUP SISWA ---
Route::middleware(['auth', 'role:siswa'])->group(function () {
    Route::get('/siswa', [StudentExamController::class, 'index'])->name('student.dashboard');
    Route::get('/exam/{exam}/run', [StudentExamController::class, 'run'])->name('student.exam.run');
    Route::post('/exam/save-answer', [StudentExamController::class, 'saveAnswer'])->name('student.exam.save');
    // routes/web.php
    Route::post('/exam/{exam}/finish', [StudentExamController::class, 'finish'])->name('student.exam.finish');
});
require __DIR__.'/auth.php';
