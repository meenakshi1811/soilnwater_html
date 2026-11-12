<?php

// Admin: a school's or institute's portal pages (profile, public page, diary, fee structure,
// jobs), loaded inside admin/schools/{managedInstitute}/manage and
// admin/institutes/{managedInstitute}/manage

use App\Http\Controllers\Admin\InstituteReviewController;
use App\Http\Controllers\Institute\InstituteDiaryController;
use App\Http\Controllers\Institute\InstituteDiaryHolidayController;
use App\Http\Controllers\Institute\InstituteFeeStructureController;
use App\Http\Controllers\Institute\InstituteJobController;
use App\Http\Controllers\Institute\InstituteScholarshipController;
use App\Http\Controllers\Institute\InstituteLeaveRuleController;
use App\Http\Controllers\Institute\InstituteNoticeController;
use App\Http\Controllers\Institute\InstituteProfileController as PortalInstituteProfileController;
use App\Http\Controllers\Institute\InstitutePublicContentController;
use App\Http\Controllers\Institute\InstitutePublicPageController;
use Illuminate\Support\Facades\Route;

Route::get('/profile', [PortalInstituteProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile', [PortalInstituteProfileController::class, 'update'])->name('profile.update');
Route::get('/public-page', [InstitutePublicPageController::class, 'edit'])->name('public-page.edit');
Route::post('/notices', [InstituteNoticeController::class, 'store'])->name('notices.store');
Route::delete('/notices/{notice}', [InstituteNoticeController::class, 'destroy'])->name('notices.destroy');
Route::post('/achievements', [InstitutePublicContentController::class, 'storeAchievement'])->name('achievements.store');
Route::delete('/achievements/{achievement}', [InstitutePublicContentController::class, 'destroyAchievement'])->name('achievements.destroy');
Route::post('/performers', [InstitutePublicContentController::class, 'storePerformer'])->name('performers.store');
Route::delete('/performers/{performer}', [InstitutePublicContentController::class, 'destroyPerformer'])->name('performers.destroy');
Route::post('/classes', [InstitutePublicContentController::class, 'storeClass'])->name('classes.store');
Route::delete('/classes/{class}', [InstitutePublicContentController::class, 'destroyClass'])->name('classes.destroy');
Route::post('/books', [InstitutePublicContentController::class, 'storeBook'])->name('books.store');
Route::delete('/books/{book}', [InstitutePublicContentController::class, 'destroyBook'])->name('books.destroy');
Route::get('/diary', [InstituteDiaryController::class, 'index'])->name('diary.index');
Route::post('/diary/holidays', [InstituteDiaryHolidayController::class, 'store'])->name('diary.holidays.store');
Route::put('/diary/holidays/{holiday}', [InstituteDiaryHolidayController::class, 'update'])->name('diary.holidays.update');
Route::delete('/diary/holidays/{holiday}', [InstituteDiaryHolidayController::class, 'destroy'])->name('diary.holidays.destroy');
Route::post('/diary/leave-rules', [InstituteLeaveRuleController::class, 'store'])->name('diary.leave-rules.store');
Route::put('/diary/leave-rules/{leaveRule}', [InstituteLeaveRuleController::class, 'update'])->name('diary.leave-rules.update');
Route::delete('/diary/leave-rules/{leaveRule}', [InstituteLeaveRuleController::class, 'destroy'])->name('diary.leave-rules.destroy');
Route::get('/fee-structure', [InstituteFeeStructureController::class, 'edit'])->name('fee-structure.edit');
Route::put('/fee-structure', [InstituteFeeStructureController::class, 'update'])->name('fee-structure.update');
// (the admin's only: removing a visitor's review)
Route::delete('/reviews/{review}', [InstituteReviewController::class, 'destroy'])->name('reviews.destroy');
Route::get('/jobs', [InstituteJobController::class, 'index'])->name('jobs.index');
Route::post('/jobs', [InstituteJobController::class, 'store'])->name('jobs.store');
Route::put('/jobs/{job}', [InstituteJobController::class, 'update'])->name('jobs.update');
Route::delete('/jobs/{job}', [InstituteJobController::class, 'destroy'])->name('jobs.destroy');
Route::patch('/jobs/applications/{application}', [InstituteJobController::class, 'updateApplication'])->name('jobs.applications.update');
Route::get('/scholarships', [InstituteScholarshipController::class, 'index'])->name('scholarships.index');
Route::post('/scholarships', [InstituteScholarshipController::class, 'store'])->name('scholarships.store');
Route::put('/scholarships/{scholarship}', [InstituteScholarshipController::class, 'update'])->name('scholarships.update');
Route::delete('/scholarships/{scholarship}', [InstituteScholarshipController::class, 'destroy'])->name('scholarships.destroy');
