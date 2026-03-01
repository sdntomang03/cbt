<?php

use App\Http\Controllers\Admin\ExamSessionController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\ProctorController;
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
        // Manajemen Sekolah (CRUD AJAX)
        Route::resource('schools', SchoolController::class)->except(['create', 'show', 'edit']);
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

        Route::get('exam-sessions/{exam_session}/students', [ExamSessionController::class, 'studentIndex'])->name('exam-sessions.students.index');
        Route::post('exam-sessions/{exam_session}/students', [ExamSessionController::class, 'studentStore'])->name('exam-sessions.students.store');
        Route::delete('exam-sessions/{examSession}/students/mass-destroy', [ExamSessionController::class, 'destroyMass'])
            ->name('exam-sessions.students.destroyMass');
        Route::get('/exams/{exam}/export', [ExamController::class, 'exportGrades'])->name('exams.export');

    });

// --- GROUP SISWA ---
Route::middleware(['auth', 'verified', 'role:siswa'])->group(function () {
    Route::get('/siswa', [StudentExamController::class, 'index'])->name('student.dashboard');
    Route::get('/exam/{exam}/run', [StudentExamController::class, 'run'])->name('student.exam.run');
    Route::post('/exam/save-answer', [StudentExamController::class, 'saveAnswer'])->name('student.exam.save');
    // routes/web.php
    Route::post('/exam/{exam}/finish', [StudentExamController::class, 'finish'])->name('student.exam.finish');
    Route::post('/exam/record-violation', [StudentExamController::class, 'recordViolation'])->name('student.exam.violation');
    // Pastikan berada di dalam middleware yang melindungi route untuk siswa
    Route::middleware(['auth'])->group(function () {
        // 1. Route untuk MENAMPILKAN halaman verifikasi token
        Route::get('/exam/{exam}/verify', [StudentExamController::class, 'showVerifyPage'])
            ->name('student.exam.verify.show');

        // 2. Route untuk MEMPROSES token (saat form disubmit)
        Route::post('/exam/{exam}/verify', [StudentExamController::class, 'processToken'])
            ->name('student.exam.verify.process');
    });
});

// Anda bisa menyesuaikan middleware rolenya, misal: 'role:admin|guru|proktor'
Route::middleware(['auth', 'role:admin|guru'])->prefix('proctor')->name('proctor.')->group(function () {

    // Daftar Sesi Ujian Hari Ini
    Route::get('/sessions', [ProctorController::class, 'index'])->name('index');

    // Halaman Monitoring Real-time
    Route::get('/sessions/{exam_session}/monitor', [ProctorController::class, 'show'])->name('monitor');

    // Aksi Pengawas
    Route::post('/sessions/{exam_session}/unlock/{student}', [ProctorController::class, 'unlock'])->name('unlock');
    Route::post('/sessions/{exam_session}/force-finish/{student}', [ProctorController::class, 'forceFinish'])->name('force-finish');
    Route::post('/sessions/{exam_session}/reset/{student}', [ProctorController::class, 'reset'])->name('reset');

});
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    // Route khusus Import Excel (Harus diletakkan DI ATAS route resource)
    Route::post('/users/import', [UserController::class, 'importExcel'])->name('users.import');
    Route::get('/users/download-template', [UserController::class, 'downloadTemplate'])->name('users.download-template');
    // Route CRUD otomatis (index, create, store, edit, update, destroy)
    Route::resource('users', UserController::class);

});
require __DIR__.'/auth.php';
