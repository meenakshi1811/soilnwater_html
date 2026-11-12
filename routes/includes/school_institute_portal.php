<?php

use App\Http\Controllers\Institute\InstituteDashboardController;
use App\Http\Controllers\Institute\InstituteDiaryController;
use App\Http\Controllers\Institute\InstituteDiaryHolidayController;
use App\Http\Controllers\Institute\InstituteEngagementPortalController;
use App\Http\Controllers\Institute\InstituteEnquiryController;
use App\Http\Controllers\Institute\InstituteFeeStructureController;
use App\Http\Controllers\Institute\InstituteLeaveRuleController;
use App\Http\Controllers\Institute\InstituteNoticeController;
use App\Http\Controllers\Institute\InstituteProfileController as PortalInstituteProfileController;
use App\Http\Controllers\Institute\InstituteJobController;
use App\Http\Controllers\Institute\InstituteScholarshipController;
use App\Http\Controllers\Institute\InstitutePublicContentController;
use App\Http\Controllers\Institute\InstitutePublicPageController;
use Illuminate\Support\Facades\Route;

// The same portal for each kind of account: /school/..., /college/..., /institute/... (route names
// school.*, college.*, institute.*), behind its "{role}.account" and "{role}" middleware
foreach (['school', 'college', 'institute'] as $role) {
    Route::prefix($role)->name($role.'.')->middleware([$role.'.account'])->group(function () use ($role): void {
        Route::get('/dashboard', [InstituteDashboardController::class, 'dashboard'])->middleware($role)->name('dashboard');
        Route::get('/profile', [PortalInstituteProfileController::class, 'edit'])->middleware($role)->name('profile.edit');
        Route::put('/profile', [PortalInstituteProfileController::class, 'update'])->middleware($role)->name('profile.update');
        Route::get('/public-page', [InstitutePublicPageController::class, 'edit'])->middleware($role)->name('public-page.edit');
        Route::get('/enquiries', [InstituteEnquiryController::class, 'index'])->middleware($role)->name('enquiries.index');
        Route::get('/jobs', [InstituteJobController::class, 'index'])->middleware($role)->name('jobs.index');
        Route::post('/jobs', [InstituteJobController::class, 'store'])->middleware($role)->name('jobs.store');
        Route::put('/jobs/{job}', [InstituteJobController::class, 'update'])->middleware($role)->name('jobs.update');
        Route::delete('/jobs/{job}', [InstituteJobController::class, 'destroy'])->middleware($role)->name('jobs.destroy');
        Route::patch('/jobs/applications/{application}', [InstituteJobController::class, 'updateApplication'])->middleware($role)->name('jobs.applications.update');
        Route::get('/scholarships', [InstituteScholarshipController::class, 'index'])->middleware($role)->name('scholarships.index');
        Route::post('/scholarships', [InstituteScholarshipController::class, 'store'])->middleware($role)->name('scholarships.store');
        Route::put('/scholarships/{scholarship}', [InstituteScholarshipController::class, 'update'])->middleware($role)->name('scholarships.update');
        Route::delete('/scholarships/{scholarship}', [InstituteScholarshipController::class, 'destroy'])->middleware($role)->name('scholarships.destroy');
        Route::get('/engagement', [InstituteEngagementPortalController::class, 'index'])->middleware($role)->name('engagement.index');
        Route::get('/diary', [InstituteDiaryController::class, 'index'])->middleware($role)->name('diary.index');
        Route::get('/fee-structure', [InstituteFeeStructureController::class, 'edit'])->middleware($role)->name('fee-structure.edit');
        Route::put('/fee-structure', [InstituteFeeStructureController::class, 'update'])->middleware($role)->name('fee-structure.update');
        Route::post('/diary/holidays', [InstituteDiaryHolidayController::class, 'store'])->middleware($role)->name('diary.holidays.store');
        Route::put('/diary/holidays/{holiday}', [InstituteDiaryHolidayController::class, 'update'])->middleware($role)->name('diary.holidays.update');
        Route::delete('/diary/holidays/{holiday}', [InstituteDiaryHolidayController::class, 'destroy'])->middleware($role)->name('diary.holidays.destroy');
        Route::post('/diary/leave-rules', [InstituteLeaveRuleController::class, 'store'])->middleware($role)->name('diary.leave-rules.store');
        Route::put('/diary/leave-rules/{leaveRule}', [InstituteLeaveRuleController::class, 'update'])->middleware($role)->name('diary.leave-rules.update');
        Route::delete('/diary/leave-rules/{leaveRule}', [InstituteLeaveRuleController::class, 'destroy'])->middleware($role)->name('diary.leave-rules.destroy');
        Route::post('/notices', [InstituteNoticeController::class, 'store'])->middleware($role)->name('notices.store');
        Route::delete('/notices/{notice}', [InstituteNoticeController::class, 'destroy'])->middleware($role)->name('notices.destroy');
        Route::post('/achievements', [InstitutePublicContentController::class, 'storeAchievement'])->middleware($role)->name('achievements.store');
        Route::delete('/achievements/{achievement}', [InstitutePublicContentController::class, 'destroyAchievement'])->middleware($role)->name('achievements.destroy');
        Route::post('/performers', [InstitutePublicContentController::class, 'storePerformer'])->middleware($role)->name('performers.store');
        Route::delete('/performers/{performer}', [InstitutePublicContentController::class, 'destroyPerformer'])->middleware($role)->name('performers.destroy');
        Route::post('/classes', [InstitutePublicContentController::class, 'storeClass'])->middleware($role)->name('classes.store');
        Route::delete('/classes/{class}', [InstitutePublicContentController::class, 'destroyClass'])->middleware($role)->name('classes.destroy');
        Route::post('/books', [InstitutePublicContentController::class, 'storeBook'])->middleware($role)->name('books.store');
        Route::delete('/books/{book}', [InstitutePublicContentController::class, 'destroyBook'])->middleware($role)->name('books.destroy');
    });
}
