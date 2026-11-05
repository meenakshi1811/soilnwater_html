<?php

// Admin: a teacher / tutor's portal pages (profile and tuition), loaded inside
// admin/educators/{managedEducator}/manage

use App\Http\Controllers\Educator\EducatorProfileController;
use App\Http\Controllers\Educator\EducatorTuitionController;
use Illuminate\Support\Facades\Route;

Route::get('/profile', [EducatorProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile', [EducatorProfileController::class, 'update'])->name('profile.update');
Route::get('/tuition', [EducatorTuitionController::class, 'edit'])->name('tuition.edit');
Route::put('/tuition', [EducatorTuitionController::class, 'update'])->name('tuition.update');
