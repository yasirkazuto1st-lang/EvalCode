<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/dashboard', function () {
    // For UAT, this acts as the student dashboard
    return view('mahasiswa.dashboard');
})->name('dashboard');

Route::get('/ujian/detail', function () {
    return view('mahasiswa.ujian.detail');
})->name('ujian.detail');

Route::get('/workspace', function () {
    return view('mahasiswa.workspace');
})->name('workspace');

Route::get('/admin', function () {
    return view('admin.dashboard');
})->name('admin');

Route::get('/pengawas', function () {
    return view('pengawas.dashboard');
})->name('pengawas.dashboard');

Route::get('/pengawas/ujian/detail', [App\Http\Controllers\PengawasUjianController::class, 'detail'])->name('pengawas.ujian.detail');
Route::get('/pengawas/ujian/soal', [App\Http\Controllers\PengawasUjianController::class, 'soal'])->name('pengawas.ujian.soal');
Route::get('/pengawas/password', [App\Http\Controllers\PengawasUjianController::class, 'password'])->name('pengawas.password');

// Admin routes
Route::get('/admin', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/admin/ujian', [App\Http\Controllers\AdminController::class, 'exams'])->name('admin.ujian.index');
Route::get('/admin/ujian/{id}', [App\Http\Controllers\AdminController::class, 'examDetail'])->name('admin.ujian.detail');
Route::get('/admin/ujian/{examId}/soal/{soalId}', [App\Http\Controllers\AdminController::class, 'soalDetail'])->name('admin.ujian.soal.detail');
Route::get('/admin/users', [App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
Route::post('/admin/users', [App\Http\Controllers\AdminController::class, 'storeUser'])->name('admin.users.store');
Route::put('/admin/users/{id}', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.users.update');
Route::delete('/admin/users/{id}', [App\Http\Controllers\AdminController::class, 'destroyUser'])->name('admin.users.destroy');
Route::get('/admin/password', [App\Http\Controllers\AdminController::class, 'password'])->name('admin.password');


Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
