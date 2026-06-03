<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PengawasUjianController;

// ==========================================
// TEMPORARY DEPLOYMENT ROUTES (Hapus setelah deploy!)
// ==========================================
Route::get('/deploy-clear-cache', function () {
    Illuminate\Support\Facades\Artisan::call('config:clear');
    Illuminate\Support\Facades\Artisan::call('route:clear');
    Illuminate\Support\Facades\Artisan::call('view:clear');
    Illuminate\Support\Facades\Artisan::call('cache:clear');
    return 'Semua cache berhasil dihapus!';
});

Route::get('/deploy-link-storage', function () {
    Illuminate\Support\Facades\Artisan::call('storage:link');
    return 'Storage link berhasil dibuat!';
});

// ==========================================
// PUBLIC ROUTES (Guest Only)
// ==========================================
Route::get('/', function () {
    return redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ==========================================
// AUTHENTICATED ROUTES
// ==========================================
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/password/update', [AuthController::class, 'updatePassword'])->name('password.update');

    Route::get('/check-session', function () {
        return response()->json(['status' => 'valid']);
    })->name('check.session');

    Route::get('/ujian/{id}/status', [\App\Http\Controllers\MahasiswaUjianController::class, 'checkStatus'])->name('ujian.status');

    // ------------------------------------------
    // MAHASISWA ROUTES
    // ------------------------------------------
    Route::middleware('role:Mahasiswa')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\MahasiswaUjianController::class, 'dashboard'])->name('dashboard');
        Route::get('/ujian/{id}', [\App\Http\Controllers\MahasiswaUjianController::class, 'detail'])->name('ujian.detail');
        Route::post('/ujian/{id}/join', [\App\Http\Controllers\MahasiswaUjianController::class, 'joinExam'])->name('ujian.join');
        Route::get('/ujian/{examId}/soal/{soalId}/workspace', [\App\Http\Controllers\MahasiswaUjianController::class, 'workspace'])->name('workspace');
        Route::post('/ujian/{examId}/soal/{soalId}/workspace/submit', [\App\Http\Controllers\MahasiswaUjianController::class, 'submitCode'])->name('workspace.submit');
    });

    // ------------------------------------------
    // PENGAWAS ROUTES
    // ------------------------------------------
    Route::middleware('role:Pengawas')->prefix('pengawas')->group(function () {
        Route::get('/', [PengawasUjianController::class, 'dashboard'])->name('pengawas.dashboard');
        Route::get('/ujian/{id}', [PengawasUjianController::class, 'detail'])->name('pengawas.ujian.detail');
        Route::post('/ujian/{id}/start', [PengawasUjianController::class, 'startExam'])->name('pengawas.ujian.start');
        Route::post('/ujian/{id}/end', [PengawasUjianController::class, 'endExam'])->name('pengawas.ujian.end');
        Route::post('/ujian/{id}/finish', [PengawasUjianController::class, 'finishExam'])->name('pengawas.ujian.finish');
        Route::get('/ujian/{examId}/soal/{soalId}', [PengawasUjianController::class, 'soal'])->name('pengawas.ujian.soal');
        Route::get('/ujian/{examId}/peserta/{userId}/riwayat', [PengawasUjianController::class, 'pesertaRiwayat'])->name('pengawas.ujian.peserta.riwayat');
        Route::post('/ujian/{examId}/peserta/{userId}/soal/{soalId}/reset-attempts', [PengawasUjianController::class, 'resetAttempts'])->name('pengawas.ujian.peserta.reset-attempts');
        Route::post('/submission/{id}/override', [PengawasUjianController::class, 'overrideScore'])->name('pengawas.submission.override');
        Route::delete('/submission/{id}', [PengawasUjianController::class, 'destroySubmission'])->name('pengawas.submission.destroy');

        // Token API (JSON) - for real-time JS polling
        Route::post('/ujian/{id}/token/refresh', [PengawasUjianController::class, 'refreshToken'])->name('pengawas.token.refresh');
        Route::get('/ujian/{id}/token/current', [PengawasUjianController::class, 'currentToken'])->name('pengawas.token.current');

        Route::get('/password', [PengawasUjianController::class, 'password'])->name('pengawas.password');
    });

    // ------------------------------------------
    // ADMIN ROUTES
    // ------------------------------------------
    Route::middleware('role:Admin')->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        
        // Ujian CRUD
        Route::get('/ujian', [AdminController::class, 'exams'])->name('admin.ujian.index');
        Route::post('/ujian', [AdminController::class, 'storeExam'])->name('admin.ujian.store');
        Route::put('/ujian/{id}', [AdminController::class, 'updateExam'])->name('admin.ujian.update');
        Route::delete('/ujian/{id}', [AdminController::class, 'destroyExam'])->name('admin.ujian.destroy');
        Route::get('/ujian/{id}', [AdminController::class, 'examDetail'])->name('admin.ujian.detail');
        Route::get('/ujian/{examId}/peserta/{userId}/riwayat', [AdminController::class, 'pesertaRiwayat'])->name('admin.ujian.peserta.riwayat');
        Route::get('/ujian/{id}/export', [AdminController::class, 'exportExcel'])->name('admin.ujian.export');
        Route::post('/submission/{id}/override', [AdminController::class, 'overrideScore'])->name('admin.submission.override');

        // Soal CRUD
        Route::post('/ujian/{examId}/soal', [AdminController::class, 'storeSoal'])->name('admin.soal.store');
        Route::post('/ujian/{examId}/soal/{soalId}', [AdminController::class, 'updateSoal'])->name('admin.soal.update');
        Route::delete('/ujian/{examId}/soal/{soalId}', [AdminController::class, 'destroySoal'])->name('admin.soal.destroy');
        Route::get('/ujian/{examId}/soal/{soalId}', [AdminController::class, 'soalDetail'])->name('admin.ujian.soal.detail');

        // TestCase CRUD
        Route::post('/ujian/{examId}/soal/{soalId}/testcase', [AdminController::class, 'storeTestCase'])->name('admin.testcase.store');
        Route::put('/ujian/{examId}/soal/{soalId}/testcase/{tcId}', [AdminController::class, 'updateTestCase'])->name('admin.testcase.update');
        Route::delete('/ujian/{examId}/soal/{soalId}/testcase/{tcId}', [AdminController::class, 'destroyTestCase'])->name('admin.testcase.destroy');

        // User CRUD
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
        Route::get('/password', [AdminController::class, 'password'])->name('admin.password');
    });
});
