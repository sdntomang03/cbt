<?php

use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\DashboardController; // <-- Pastikan ini di-import
use App\Http\Controllers\Admin\ExamSessionController;
use App\Http\Controllers\Admin\ItemAnalysisController;
use App\Http\Controllers\Admin\MathExamController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RegistrationSettingController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\KawanBacaController;
use App\Http\Controllers\KawanHitungController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProctorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicExamController;
use App\Http\Controllers\QuestionAjaxController;
use App\Http\Controllers\SitemapController;
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
Route::get('/ranking-nasional', [PublicExamController::class, 'nationalRanking'])->name('public.ranking.nasional');
// ==================================================================
// ROUTE DASHBOARD (PINTU GERBANG)
// ==================================================================
Route::get('/dashboard', function () {
    $user = auth()->user();

    // Jika Siswa, arahkan ke dashboard ujiannya sendiri
    if ($user->hasRole('siswa')) {
        return redirect()->route('student.dashboard');
    }

    // Jika Admin, Operator, atau Guru, arahkan ke Dashboard Ikhtisar
    return redirect()->route('admin.dashboard');
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
// ==================================================================
Route::middleware(['auth', 'role:admin|operator|guru'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // --- PERBAIKAN 1: Rute Asli Dashboard Admin Ditambahkan ---
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // --- PERBAIKAN 2: Manajemen Keamanan (Khusus Super Admin) ---
        Route::middleware('role:admin')->group(function () {
            Route::resource('roles', RoleController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('permissions', PermissionController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::get('permissions/{permission}/users', [PermissionController::class, 'users'])->name('permissions.users');
        });

        // --- Manajemen Sekolah (Admin & Operator) ---
        Route::middleware('role:admin|operator')->group(function () {
            Route::get('/schools/export', [SchoolController::class, 'export'])->name('schools.export');
            Route::delete('/schools/bulk-delete', [SchoolController::class, 'bulkDelete'])->name('schools.bulk-delete');
            Route::resource('schools', SchoolController::class)->except(['create', 'show', 'edit']);
            Route::get('schools/{school}/details', [SchoolController::class, 'showDetails'])->name('schools.details');

            // Pengaturan Registrasi
            Route::get('settings/registration', [RegistrationSettingController::class, 'edit'])->name('settings.registration');
            Route::put('settings/registration', [RegistrationSettingController::class, 'update'])->name('settings.registration.update');
        });

        // --- Manajemen Kelas ---
        Route::resource('classrooms', ClassroomController::class)->except(['show', 'create', 'edit']);
        Route::get('classrooms/{classroom}/students', [ClassroomController::class, 'manageStudents'])->name('classrooms.students');
        Route::put('classrooms/{classroom}/students', [ClassroomController::class, 'syncStudents'])->name('classrooms.sync-students');
        Route::post('classrooms/{classroom}/students/attach', [ClassroomController::class, 'attachStudents'])->name('classrooms.attach-students');
        Route::delete('classrooms/{classroom}/students/{student}/detach', [ClassroomController::class, 'detachStudent'])->name('classrooms.detach-student');

        // --- Manajemen Ujian (CBT & Bank Soal) ---
        Route::get('/exams/{exam}/export', [ExamController::class, 'exportGrades'])->name('exams.export');
        Route::resource('exams', ExamController::class);
        Route::post('/exam-types', [ExamController::class, 'storeType'])->name('exam-types.store');
        Route::post('/preview', [ExamController::class, 'preview'])->name('preview');
        Route::get('/live-preview', [ExamController::class, 'livePreview'])->name('live-preview');
        Route::get('/chart-generator', [SoalController::class, 'chartGenerator'])
            ->name('soal.chart.generator');

        Route::resource('exams.soal', SoalController::class)->except(['show']);

        // Import/Export Soal
        Route::get('/soal/download-template', [SoalController::class, 'downloadTemplate'])->name('soal.template');
        Route::post('/exams/{exam}/soal/import', [SoalController::class, 'import'])->name('exams.soal.import');
        Route::get('/exams/{exam}/import-json', [SoalController::class, 'showImportJson'])->name('soal.import_json_view');
        Route::post('/exams/{exam}/import-json/preview', [SoalController::class, 'previewImportJson'])->name('soal.import_json_preview');
        Route::post('/exams/{exam}/import-json/store', [SoalController::class, 'storeImportJson'])->name('soal.import_json_store');
        Route::get('exams/{exam}/soal/bank', [SoalController::class, 'showBankSoal'])->name('exams.soal.bank');
        Route::post('exams/{exam}/soal/bank', [SoalController::class, 'attachBankSoal'])->name('exams.soal.bank.store');
        // Manajemen Soal (AJAX)
        Route::get('/exams/{exam}/questions', [QuestionAjaxController::class, 'index'])->name('ajax.questions.index');
        Route::post('/exams/{exam}/questions', [QuestionAjaxController::class, 'store'])->name('ajax.questions.store');
        Route::put('/questions/{question}', [QuestionAjaxController::class, 'update'])->name('ajax.questions.update');
        Route::delete('/questions/{question}', [QuestionAjaxController::class, 'destroy'])->name('ajax.questions.destroy');

        // Upload Gambar (Summernote/CKEditor)
        Route::post('/upload-image', [ImageUploadController::class, 'store'])->name('image.upload');

        // Manajemen Sesi Ujian (Jadwal)
        Route::post('exam-sessions/{exam_session}/regenerate-token', [ExamSessionController::class, 'regenerateToken'])->name('exam-sessions.regenerate-token');
        Route::get('exam-sessions/{exam_session}/students', [ExamSessionController::class, 'studentIndex'])->name('exam-sessions.students.index');
        Route::post('exam-sessions/{exam_session}/students', [ExamSessionController::class, 'studentStore'])->name('exam-sessions.students.store');
        Route::delete('exam-sessions/{examSession}/students/mass-destroy', [ExamSessionController::class, 'destroyMass'])->name('exam-sessions.students.destroyMass');
        Route::resource('exam-sessions', ExamSessionController::class);

        // Ujian Matematika
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
        Route::patch('/math/{id}/toggle-explanation', [MathExamController::class, 'toggleExplanation'])->name('math.toggle-explanation');

        // Manajemen Users
        Route::delete('/users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
        Route::get('/users/export-selected', [UserController::class, 'exportSelected'])->name('users.export-selected');
        Route::post('/users/import', [UserController::class, 'importExcel'])->name('users.import');
        Route::get('/users/download-template', [UserController::class, 'downloadTemplate'])->name('users.download-template');
        Route::resource('users', UserController::class);

        // --- PERBAIKAN 3: Perbaikan Nama Route agar tidak ganda ---
        Route::post('/users/{user}/update-role', [UserController::class, 'updateRole'])->name('users.update_role');

        Route::resource('/modules', ModuleController::class)->names('modules');

    });

// ==================================================================
// GROUP PROCTOR / PENGAWAS
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
// ==================================================================
Route::middleware(['auth', 'verified', 'role:siswa'])->group(function () {

    // Dashboard Utama Siswa
    Route::get('/siswa', [StudentExamController::class, 'index'])->name('student.index');
    Route::get('/home', [StudentExamController::class, 'dashboard'])->name('student.dashboard');

    // Ujian Biasa (Reguler)
    Route::get('/exam/{exam}/verify', [StudentExamController::class, 'showVerifyPage'])->name('student.exam.verify.show');
    Route::post('/exam/{exam}/verify', [StudentExamController::class, 'processToken'])->name('student.exam.verify.process');
    Route::get('/exam/{exam}/run', [StudentExamController::class, 'run'])->name('student.exam.run');
    Route::post('/exam/save-answer', [StudentExamController::class, 'saveAnswer'])->name('student.exam.save');
    Route::post('/exam/{exam}/finish', [StudentExamController::class, 'finish'])->name('student.exam.finish');
    Route::post('/exam/record-violation', [StudentExamController::class, 'recordViolation'])->name('student.exam.violation');
    Route::get('/exam/{exam}/status', [StudentExamController::class, 'checkStatus'])
        ->name('student.exam.status');
    Route::get('/student/exams/{session}/explanation', [ExamSessionController::class, 'explanation'])
        ->name('student.exams.explanation');
    // Ubah {exam} menjadi {hashed_exam_id}
    Route::get('/student/exam/{exam}/question/{question_id}', [StudentExamController::class, 'fetchSingleQuestion'])
        ->name('student.exam.fetch_question');
    // Ujian Matematika (Siswa)
    Route::get('/math-exam/', [StudentMathExamController::class, 'index'])->name('student.math.index');
    Route::get('/math-exam/{id}/run', [StudentMathExamController::class, 'run'])->name('student.math.run');
    Route::post('/math-exam/{id}/submit', [StudentMathExamController::class, 'submit'])->name('student.math.submit');
    Route::get('/math-exam/{id}/result', [StudentMathExamController::class, 'result'])->name('student.math.result');
    Route::post('/math-exam/{id}/autosave', [StudentMathExamController::class, 'autosave'])->name('student.math.autosave');

});

// ==================================================================
// GROUP KAWAN BELAJAR (Publik / Auth Opsional)
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

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:teacher|admin'])->group(function () {

    // Analisis Butir Soal
    Route::get('/exams/{exam}/analysis', [ItemAnalysisController::class, 'index'])
        ->name('analysis.index');

    Route::get('/exams/{exam}/analysis/{session}', [ItemAnalysisController::class, 'show'])
        ->name('analysis.show');

    Route::get('/exams/{exam}/analysis/{session}/export', [ItemAnalysisController::class, 'export'])
        ->name('analysis.export');
    Route::post('/upload-image', [SoalController::class, 'uploadImage'])->name('soal.upload-image');
});

Route::group(['prefix' => 'tryout', 'as' => 'public.exams.'], function () {
    Route::get('/', [PublicExamController::class, 'index'])->name('index');
    Route::get('/{exam}', [PublicExamController::class, 'show'])->name('show'); // Menampilkan/Menjalankan Ujian
    Route::post('/{exam}/answers', [PublicExamController::class, 'storeAnswer'])->name('store_answer');
    Route::post('/{exam}/violations', [PublicExamController::class, 'recordViolation'])->name('record_violation');
    Route::post('/{exam}/finish', [PublicExamController::class, 'finish'])->name('finish');

    Route::get('/{exam}/verify', [PublicExamController::class, 'verify'])->name('verify');
    Route::post('/{exam}/verify', [PublicExamController::class, 'processVerify'])->name('process_verify');

    Route::post('/{exam}/restart', [PublicExamController::class, 'restart'])->name('restart');
    Route::get('/{exam}/result', [PublicExamController::class, 'result'])->name('result');
    Route::get('/{exam}/ranking', [PublicExamController::class, 'ranking'])
        ->name('ranking');
    Route::get('/detail/{slug}', [PublicExamController::class, 'detail'])->name('detail');
});

Route::get('/materi-belajar', [ModuleController::class, 'studentIndex'])->name('public.modules.index');
Route::get('/materi-belajar/{slug}', [ModuleController::class, 'studentShow'])->name('public.modules.show');

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
require __DIR__.'/auth.php';
