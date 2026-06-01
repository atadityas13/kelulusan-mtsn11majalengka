<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InteractionController;

/*
|--------------------------------------------------------------------------
| Web Routes - Kelulusan MTsN 11 Majalengka
|--------------------------------------------------------------------------
*/

// ========================================================
// 1. RUTE PUBLIK (HOMEPAGE & SECURE LOOKUP)
// ========================================================
Route::match(['get', 'post'], '/', [HomeController::class, 'index'])->name('home');
Route::get('/lookup/nopes', [HomeController::class, 'lookupNopes'])->name('lookup.nopes');
Route::get('/lookup/nisn', [HomeController::class, 'lookupNisn'])->name('lookup.nisn');

// ========================================================
// 2. RUTE INTERAKSI SOSIAL (AJAX)
// ========================================================
Route::post('/interaction/check-history', [InteractionController::class, 'saveCheckHistory'])->name('interaction.history');
Route::post('/interaction/testimonial', [InteractionController::class, 'saveTestimonial'])->name('interaction.testimonial');
Route::post('/interaction/like', [InteractionController::class, 'likeItem'])->name('interaction.like');
Route::post('/interaction/comment', [InteractionController::class, 'addComment'])->name('interaction.comment');

// ========================================================
// 3. RUTE AUTENTIKASI ADMIN
// ========================================================
Route::match(['get', 'post'], '/admin/login', [AdminController::class, 'login'])->name('login');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// ========================================================
// 4. RUTE PROTEKSI DASHBOARD ADMIN (DENGAN AUTH MIDDLEWARE)
// ========================================================
Route::middleware(['auth'])->prefix('admin')->group(function () {
    
    // Dasbor Utama
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Manajemen Siswa (Students CRUD)
    Route::post('/students/add', [AdminController::class, 'addStudent'])->name('admin.student.add');
    Route::post('/students/edit/{id}', [AdminController::class, 'editStudent'])->name('admin.student.edit');
    Route::post('/students/delete/{id}', [AdminController::class, 'deleteStudent'])->name('admin.student.delete');
    Route::post('/students/import', [AdminController::class, 'importStudents'])->name('admin.student.import');

    // Manajemen Tahun Ajaran (Academic Years CRUD)
    Route::post('/academic-years/add', [AdminController::class, 'addAcademicYear'])->name('admin.year.add');
    Route::post('/academic-years/activate/{id}', [AdminController::class, 'activateAcademicYear'])->name('admin.year.activate');
    Route::post('/academic-years/delete/{id}', [AdminController::class, 'deleteAcademicYear'])->name('admin.year.delete');

    // Moderasi Testimoni Siswa
    Route::post('/testimonials/approve/{id}', [AdminController::class, 'approveTestimonial'])->name('admin.testimonial.approve');
    Route::post('/testimonials/reject/{id}', [AdminController::class, 'rejectTestimonial'])->name('admin.testimonial.reject');
    Route::post('/testimonials/delete/{id}', [AdminController::class, 'deleteTestimonial'])->name('admin.testimonial.delete');

    // Manajemen Pesan Guru
    Route::post('/teacher-messages/add', [AdminController::class, 'addTeacherMessage'])->name('admin.teacher-message.add');
    Route::post('/teacher-messages/delete/{id}', [AdminController::class, 'deleteTeacherMessage'])->name('admin.teacher-message.delete');

    // Log Riwayat & Pengaturan Sistem
    Route::post('/check-history/clear', [AdminController::class, 'clearCheckHistory'])->name('admin.history.clear');
    Route::post('/settings/save', [AdminController::class, 'saveSettings'])->name('admin.settings.save');
    Route::post('/settings/password', [AdminController::class, 'changePassword'])->name('admin.settings.password');

});
?>
