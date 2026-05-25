<?php

use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\ExamSessionController;
use App\Http\Controllers\Admin\MathExamController;
use App\Http\Controllers\Admin\RegistrationSettingController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\KawanBacaController;
use App\Http\Controllers\KawanHitungController;
use App\Http\Controllers\ProctorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionAjaxController;
use App\Http\Controllers\SoalController;
use App\Http\Controllers\Student\MathExamController as StudentMathExamController;
use App\Http\Controllers\Student\StudentExamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Redirect otomatis sesuai role saat login
Route::get('/dashboard', function () {
    $user = auth()->user();

    // OPSI 1: Melihat seluruh data user yang sedang login
    // dd($user);

    // OPSI 2: Mengecek apakah method hasRole('siswa') menghasilkan true atau false
    // dd($user->hasRole('siswa'));

    // OPSI 3: Melihat daftar semua role yang dimiliki user tersebut
    // (Jika Anda menggunakan package Spatie Permission)
    // dd($user->getRoleNames());

    if ($user->hasRole('siswa')) {
        return redirect()->route('student.dashboard');
    }

    // Admin, Operator, atau Guru diarahkan ke halaman admin (misal index ujian)
    return redirect()->route('admin.exams.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// ==================================================================
// GROUP PROFILE (Semua Role Bisa Akses)
// ==================================================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==================================================================
// GROUP ADMIN, OPERATOR, & GURU
// Operator dimasukkan agar bisa membantu manajemen sekolah & ujian
// ==================================================================
Route::middleware(['auth', 'role:admin|operator|guru'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // --- 1. Manajemen Sekolah ---
        // Biasanya Guru tidak bisa menghapus/ekspor sekolah massal, sebaiknya dipisah untuk Admin saja
        Route::middleware('role:admin|operator')->group(function () {
            Route::get('/schools/export', [SchoolController::class, 'export'])->name('schools.export');
            Route::delete('/schools/bulk-delete', [SchoolController::class, 'bulkDelete'])->name('schools.bulk-delete');
            Route::resource('schools', SchoolController::class)->except(['create', 'show', 'edit']);
            Route::get('schools/{school}/details', [SchoolController::class, 'showDetails'])->name('schools.details');

            // Pengaturan Registrasi (Hanya Admin & Operator)
            Route::get('settings/registration', [RegistrationSettingController::class, 'edit'])->name('settings.registration');
            Route::put('settings/registration', [RegistrationSettingController::class, 'update'])->name('settings.registration.update');
        });

        // --- 2. Manajemen Kelas ---
        Route::resource('classrooms', ClassroomController::class)->except(['show', 'create', 'edit']); // create/edit dihapus karena pakai modal
        Route::get('classrooms/{classroom}/students', [ClassroomController::class, 'manageStudents'])->name('classrooms.students');
        Route::put('classrooms/{classroom}/students', [ClassroomController::class, 'syncStudents'])->name('classrooms.sync-students');
        Route::post('classrooms/{classroom}/students/attach', [ClassroomController::class, 'attachStudents'])->name('classrooms.attach-students');
        Route::delete('classrooms/{classroom}/students/{student}/detach', [ClassroomController::class, 'detachStudent'])->name('classrooms.detach-student');

        // --- 3. Manajemen Ujian (CBT & Bank Soal) ---
        Route::get('/exams/{exam}/export', [ExamController::class, 'exportGrades'])->name('exams.export');
        Route::resource('exams', ExamController::class);
        Route::post('/exam-types', [ExamController::class, 'storeType'])->name('exam-types.store');

        // Nested resource Soal Utama
        Route::resource('exams.soal', SoalController::class)->except(['show']);

        // Import/Export Soal (Bisa diakses Admin/Operator/Guru)
        Route::get('/soal/download-template', [SoalController::class, 'downloadTemplate'])->name('soal.template');
        Route::post('/exams/{exam}/soal/import', [SoalController::class, 'import'])->name('exams.soal.import');
        Route::get('/exams/{exam}/import-json', [SoalController::class, 'showImportJson'])->name('soal.import_json_view');
        Route::post('/exams/{exam}/import-json/preview', [SoalController::class, 'previewImportJson'])->name('soal.import_json_preview');
        Route::post('/exams/{exam}/import-json/store', [SoalController::class, 'storeImportJson'])->name('soal.import_json_store');

        // --- 4. Manajemen Soal (AJAX) ---
        Route::get('/exams/{exam}/questions', [QuestionAjaxController::class, 'index'])->name('ajax.questions.index');
        Route::post('/exams/{exam}/questions', [QuestionAjaxController::class, 'store'])->name('ajax.questions.store');
        Route::put('/questions/{question}', [QuestionAjaxController::class, 'update'])->name('ajax.questions.update');
        Route::delete('/questions/{question}', [QuestionAjaxController::class, 'destroy'])->name('ajax.questions.destroy');

        // --- 5. Upload Gambar (Summernote/CKEditor) ---
        Route::post('/upload-image', [ImageUploadController::class, 'store'])->name('image.upload');

        // --- 6. Manajemen Sesi Ujian (Jadwal) ---
        Route::post('exam-sessions/{exam_session}/regenerate-token', [ExamSessionController::class, 'regenerateToken'])->name('exam-sessions.regenerate-token');
        Route::get('exam-sessions/{exam_session}/students', [ExamSessionController::class, 'studentIndex'])->name('exam-sessions.students.index');
        Route::post('exam-sessions/{exam_session}/students', [ExamSessionController::class, 'studentStore'])->name('exam-sessions.students.store');
        Route::delete('exam-sessions/{examSession}/students/mass-destroy', [ExamSessionController::class, 'destroyMass'])->name('exam-sessions.students.destroyMass');
        Route::resource('exam-sessions', ExamSessionController::class);

        // --- 7. Ujian Matematika (Admin/Guru) ---
        Route::get('/math-exams', [MathExamController::class, 'index'])->name('math.index');
        Route::get('/math-exams/create', [MathExamController::class, 'create'])->name('math.create');
        Route::post('/math-exams/store', [MathExamController::class, 'store'])->name('math.store');
        Route::get('/math-exams/{id}/show', [MathExamController::class, 'show'])->name('math.show');
        Route::delete('/math-exams/{id}', [MathExamController::class, 'destroy'])->name('math.destroy');
        Route::get('/math-exams/result/{examUserId}', [MathExamController::class, 'showStudentResult'])->name('math.student_result');
        Route::post('/math/reset/{examUserId}', [MathExamController::class, 'resetStudentExam'])->name('math.resetStudent');
        Route::get('/math-exams/{id}/export-recap', [MathExamController::class, 'exportRecap'])->name('math.recap_export');
        Route::get('/math-exams/result/{examUserId}/export', [MathExamController::class, 'exportStudentResult'])->name('math.student_result_export');
        Route::post('/math-exams/{id}/add-student', [MathExamController::class, 'addStudent'])->name('math.addStudent');
        Route::get('/math-exams/{id}/print', [MathExamController::class, 'printWorksheets'])->name('math.print');

        // --- 8. Manajemen Users ---
        Route::delete('/users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
        Route::get('/users/export-selected', [UserController::class, 'exportSelected'])->name('users.export-selected');
        Route::post('/users/import', [UserController::class, 'importExcel'])->name('users.import');
        Route::get('/users/download-template', [UserController::class, 'downloadTemplate'])->name('users.download-template');
        Route::resource('users', UserController::class);
        Route::patch('/math/{id}/toggle-explanation', [MathExamController::class, 'toggleExplanation'])->name('math.toggle-explanation');
    });

// ==================================================================
// GROUP PROCTOR / PENGAWAS
// Bisa diakses oleh Admin, Operator, dan Guru
// ==================================================================
Route::middleware(['auth', 'role:admin|operator|guru'])
    ->prefix('proctor')
    ->name('proctor.')
    ->group(function () {
        Route::get('/sessions', [ProctorController::class, 'index'])->name('index');
        Route::get('/sessions/{exam_session}/monitor', [ProctorController::class, 'show'])->name('monitor');
        Route::post('/sessions/{exam_session}/unlock/{student}', [ProctorController::class, 'unlock'])->name('unlock');
        Route::post('/sessions/{exam_session}/force-finish/{student}', [ProctorController::class, 'forceFinish'])->name('force-finish');
        Route::post('/sessions/{exam_session}/reset/{student}', [ProctorController::class, 'reset'])->name('reset');
    });

// ==================================================================
// GROUP SISWA
// Hanya bisa diakses jika role = siswa
// ==================================================================
Route::middleware(['auth', 'verified', 'role:siswa'])->group(function () {

    // --- 1. Dashboard Utama Siswa ---
    Route::get('/siswa', [StudentExamController::class, 'index'])->name('student.dashboard');
    // Route untuk melihat hasil ujian siswa

    // --- 2. Ujian Biasa (Reguler) ---
    Route::get('/exam/{exam}/verify', [StudentExamController::class, 'showVerifyPage'])->name('student.exam.verify.show');
    Route::post('/exam/{exam}/verify', [StudentExamController::class, 'processToken'])->name('student.exam.verify.process');
    Route::get('/exam/{exam}/run', [StudentExamController::class, 'run'])->name('student.exam.run');
    Route::post('/exam/save-answer', [StudentExamController::class, 'saveAnswer'])->name('student.exam.save');
    Route::post('/exam/{exam}/finish', [StudentExamController::class, 'finish'])->name('student.exam.finish');
    Route::post('/exam/record-violation', [StudentExamController::class, 'recordViolation'])->name('student.exam.violation');

    // --- 3. Ujian Matematika (Siswa) ---
    Route::get('/math-exam/', [StudentMathExamController::class, 'index'])->name('student.math.index'); // Hindari konflik URL dengan math-exams admin
    Route::get('/math-exam/{id}/run', [StudentMathExamController::class, 'run'])->name('student.math.run');
    Route::post('/math-exam/{id}/submit', [StudentMathExamController::class, 'submit'])->name('student.math.submit');
    Route::get('/math-exam/{id}/result', [StudentExamController::class, 'result'])->name('student.math.result');

});

// ==================================================================
// GROUP KAWAN BELAJAR (Publik / Auth Opsional)
// Asumsi ini fitur belajar tambahan
// ==================================================================
Route::prefix('kawan-hitung')->group(function () {
    Route::get('/', [KawanHitungController::class, 'index'])->name('hitung.index');
    Route::post('/generate', [KawanHitungController::class, 'generate'])->name('hitung.generate');
    Route::get('/latihan', [KawanHitungController::class, 'latihan'])->name('hitung.latihan');
    Route::post('/submit', [KawanHitungController::class, 'submit'])->name('hitung.submit');
    Route::get('/belajar', [KawanHitungController::class, 'belajar'])->name('hitung.belajar');
});

Route::prefix('kawan-baca')->group(function () {
    Route::get('/', [KawanBacaController::class, 'index'])->name('baca.index');
    Route::post('/generate', [KawanBacaController::class, 'generate'])->name('baca.generate');
    Route::get('/latihan', [KawanBacaController::class, 'latihan'])->name('baca.latihan');
});

require __DIR__.'/auth.php';
