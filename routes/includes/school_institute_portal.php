<?php

use App\Http\Controllers\Institute\InstituteDashboardController;
use App\Http\Controllers\Institute\InstituteDiaryController;
use App\Http\Controllers\Institute\InstituteDiaryHolidayController;
use App\Http\Controllers\Institute\InstituteEngagementPortalController;
use App\Http\Controllers\Institute\InstituteEnquiryController;
use App\Http\Controllers\Institute\InstituteLeaveRuleController;
use App\Http\Controllers\Institute\InstituteNoticeController;
use App\Http\Controllers\Institute\InstituteProfileController as PortalInstituteProfileController;
use App\Http\Controllers\Institute\InstitutePublicContentController;
use App\Http\Controllers\Institute\InstitutePublicPageController;
use Illuminate\Support\Facades\Route;

Route::prefix('school')->name('school.')->middleware(['school.account'])->group(function (): void {
    Route::get('/dashboard', [InstituteDashboardController::class, 'dashboard'])->middleware('school')->name('dashboard');
    Route::get('/profile', [PortalInstituteProfileController::class, 'edit'])->middleware('school')->name('profile.edit');
    Route::put('/profile', [PortalInstituteProfileController::class, 'update'])->middleware('school')->name('profile.update');
    Route::get('/public-page', [InstitutePublicPageController::class, 'edit'])->middleware('school')->name('public-page.edit');
    Route::get('/enquiries', [InstituteEnquiryController::class, 'index'])->middleware('school')->name('enquiries.index');
    Route::get('/engagement', [InstituteEngagementPortalController::class, 'index'])->middleware('school')->name('engagement.index');
    Route::get('/diary', [InstituteDiaryController::class, 'index'])->middleware('school')->name('diary.index');
    Route::post('/diary/holidays', [InstituteDiaryHolidayController::class, 'store'])->middleware('school')->name('diary.holidays.store');
    Route::put('/diary/holidays/{holiday}', [InstituteDiaryHolidayController::class, 'update'])->middleware('school')->name('diary.holidays.update');
    Route::delete('/diary/holidays/{holiday}', [InstituteDiaryHolidayController::class, 'destroy'])->middleware('school')->name('diary.holidays.destroy');
    Route::post('/diary/leave-rules', [InstituteLeaveRuleController::class, 'store'])->middleware('school')->name('diary.leave-rules.store');
    Route::put('/diary/leave-rules/{leaveRule}', [InstituteLeaveRuleController::class, 'update'])->middleware('school')->name('diary.leave-rules.update');
    Route::delete('/diary/leave-rules/{leaveRule}', [InstituteLeaveRuleController::class, 'destroy'])->middleware('school')->name('diary.leave-rules.destroy');
    Route::post('/notices', [InstituteNoticeController::class, 'store'])->middleware('school')->name('notices.store');
    Route::delete('/notices/{notice}', [InstituteNoticeController::class, 'destroy'])->middleware('school')->name('notices.destroy');
    Route::post('/achievements', [InstitutePublicContentController::class, 'storeAchievement'])->middleware('school')->name('achievements.store');
    Route::delete('/achievements/{achievement}', [InstitutePublicContentController::class, 'destroyAchievement'])->middleware('school')->name('achievements.destroy');
    Route::post('/performers', [InstitutePublicContentController::class, 'storePerformer'])->middleware('school')->name('performers.store');
    Route::delete('/performers/{performer}', [InstitutePublicContentController::class, 'destroyPerformer'])->middleware('school')->name('performers.destroy');
    Route::post('/classes', [InstitutePublicContentController::class, 'storeClass'])->middleware('school')->name('classes.store');
    Route::delete('/classes/{class}', [InstitutePublicContentController::class, 'destroyClass'])->middleware('school')->name('classes.destroy');
    Route::post('/books', [InstitutePublicContentController::class, 'storeBook'])->middleware('school')->name('books.store');
    Route::delete('/books/{book}', [InstitutePublicContentController::class, 'destroyBook'])->middleware('school')->name('books.destroy');
});

Route::prefix('institute')->name('institute.')->middleware(['institute.account'])->group(function (): void {
    Route::get('/dashboard', [InstituteDashboardController::class, 'dashboard'])->middleware('institute')->name('dashboard');
    Route::get('/profile', [PortalInstituteProfileController::class, 'edit'])->middleware('institute')->name('profile.edit');
    Route::put('/profile', [PortalInstituteProfileController::class, 'update'])->middleware('institute')->name('profile.update');
    Route::get('/public-page', [InstitutePublicPageController::class, 'edit'])->middleware('institute')->name('public-page.edit');
    Route::get('/enquiries', [InstituteEnquiryController::class, 'index'])->middleware('institute')->name('enquiries.index');
    Route::get('/engagement', [InstituteEngagementPortalController::class, 'index'])->middleware('institute')->name('engagement.index');
    Route::get('/diary', [InstituteDiaryController::class, 'index'])->middleware('institute')->name('diary.index');
    Route::post('/diary/holidays', [InstituteDiaryHolidayController::class, 'store'])->middleware('institute')->name('diary.holidays.store');
    Route::put('/diary/holidays/{holiday}', [InstituteDiaryHolidayController::class, 'update'])->middleware('institute')->name('diary.holidays.update');
    Route::delete('/diary/holidays/{holiday}', [InstituteDiaryHolidayController::class, 'destroy'])->middleware('institute')->name('diary.holidays.destroy');
    Route::post('/diary/leave-rules', [InstituteLeaveRuleController::class, 'store'])->middleware('institute')->name('diary.leave-rules.store');
    Route::put('/diary/leave-rules/{leaveRule}', [InstituteLeaveRuleController::class, 'update'])->middleware('institute')->name('diary.leave-rules.update');
    Route::delete('/diary/leave-rules/{leaveRule}', [InstituteLeaveRuleController::class, 'destroy'])->middleware('institute')->name('diary.leave-rules.destroy');
    Route::post('/notices', [InstituteNoticeController::class, 'store'])->middleware('institute')->name('notices.store');
    Route::delete('/notices/{notice}', [InstituteNoticeController::class, 'destroy'])->middleware('institute')->name('notices.destroy');
    Route::post('/achievements', [InstitutePublicContentController::class, 'storeAchievement'])->middleware('institute')->name('achievements.store');
    Route::delete('/achievements/{achievement}', [InstitutePublicContentController::class, 'destroyAchievement'])->middleware('institute')->name('achievements.destroy');
    Route::post('/performers', [InstitutePublicContentController::class, 'storePerformer'])->middleware('institute')->name('performers.store');
    Route::delete('/performers/{performer}', [InstitutePublicContentController::class, 'destroyPerformer'])->middleware('institute')->name('performers.destroy');
    Route::post('/classes', [InstitutePublicContentController::class, 'storeClass'])->middleware('institute')->name('classes.store');
    Route::delete('/classes/{class}', [InstitutePublicContentController::class, 'destroyClass'])->middleware('institute')->name('classes.destroy');
    Route::post('/books', [InstitutePublicContentController::class, 'storeBook'])->middleware('institute')->name('books.store');
    Route::delete('/books/{book}', [InstitutePublicContentController::class, 'destroyBook'])->middleware('institute')->name('books.destroy');
});
