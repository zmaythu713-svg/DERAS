<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AllocationPlanController;
use App\Http\Controllers\BookNameController;
use App\Http\Controllers\CompanyContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GradeSubjectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuotaController;
use App\Http\Controllers\ResourceLookupController;
use App\Http\Controllers\SchoolSupplyController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplyDetailController;
use App\Http\Controllers\TeacherGuideController;
use App\Http\Controllers\TeacherGuideDistributionController;
use App\Http\Controllers\TeacherGuideIssueController;
use App\Http\Controllers\TeacherGuideSummaryController;
use App\Http\Controllers\TextbookController;
use App\Http\Controllers\TownshipController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::put('/profile', 'update')->name('profile.update');

        Route::get('/password/change', 'passwordEdit')
            ->name('password.edit');

        Route::put('/password/change', 'passwordUpdate')
            ->name('password.update');
    });

    // Super / users.manage only
    Route::resource('admin-users', AdminUserController::class)
        ->middleware('permission:users.manage');

    Route::resource('townships', TownshipController::class)
        ->middlewareFor('destroy', 'permission:records.delete');

    Route::resource('academic-years', AcademicYearController::class)
        ->middlewareFor('destroy', 'permission:records.delete');
    Route::post('/academic-years/rollover', [AcademicYearController::class, 'rollover'])
        ->name('academic-years.rollover')
        ->middleware('permission:academic_years.rollover');

    Route::resource('grades', GradeController::class)
        ->except(['show'])
        ->middlewareFor('destroy', 'permission:records.delete');
    Route::get('/grades/{grade}/subjects', [GradeController::class, 'getSubjects'])
        ->name('grades.subjects');

    Route::get('/grade-subjects', [GradeSubjectController::class, 'index'])
        ->name('grade-subjects.index');
    Route::get('/grade-subjects/create', [GradeSubjectController::class, 'create'])
        ->name('grade-subjects.create');
    Route::post('/grade-subjects', [GradeSubjectController::class, 'store'])
        ->name('grade-subjects.store');
    Route::get('/grade-subjects/{grade}/edit', [GradeSubjectController::class, 'edit'])
        ->name('grade-subjects.edit');
    Route::put('/grade-subjects/{grade}', [GradeSubjectController::class, 'update'])
        ->name('grade-subjects.update');
    Route::delete('/grade-subjects/{grade}', [GradeSubjectController::class, 'destroy'])
        ->name('grade-subjects.destroy')
        ->middleware('permission:records.delete');

    Route::prefix('lookups')->name('lookups.')->group(function () {
        Route::get('/categories', [ResourceLookupController::class, 'categories'])
            ->name('categories');
        Route::get('/allocation-for-textbook', [ResourceLookupController::class, 'allocationForTextbook'])
            ->name('allocation-for-textbook');
        Route::get('/previous-year-balance', [ResourceLookupController::class, 'previousYearBalance'])
            ->name('previous-year-balance');
        Route::get('/school-count', [ResourceLookupController::class, 'schoolCount'])
            ->name('school-count');
        Route::get('/school-supply-quantity', [ResourceLookupController::class, 'schoolSupplyQuantity'])
            ->name('school-supply-quantity');
        Route::get('/teacher-guide-receipt', [ResourceLookupController::class, 'teacherGuideReceipt'])
            ->name('teacher-guide-receipt');
    });

    Route::resource('book-names', BookNameController::class)
        ->middlewareFor('destroy', 'permission:records.delete');

    Route::resource('textbook', TextbookController::class)
        ->middlewareFor('destroy', 'permission:records.delete');

    Route::resource('stocks', StockController::class)
        ->middlewareFor('destroy', 'permission:records.delete');

    Route::resource('quota', QuotaController::class)
        ->middlewareFor('destroy', 'permission:records.delete');

    Route::resource('teacher-guides', TeacherGuideController::class)
        ->middlewareFor('destroy', 'permission:records.delete');

    Route::resource('company-contacts', CompanyContactController::class)
        ->middlewareFor('destroy', 'permission:records.delete');

    Route::resource('school-supplies', SchoolSupplyController::class)
        ->middlewareFor('destroy', 'permission:records.delete');

    Route::resource('supply-details', SupplyDetailController::class)
        ->middlewareFor('destroy', 'permission:records.delete');

    Route::resource(
        'teacher-guide-distributions',
        TeacherGuideDistributionController::class
    )
        ->except(['show'])
        ->middlewareFor('destroy', 'permission:records.delete');

    Route::resource(
        'teacher-guide-issues',
        TeacherGuideIssueController::class
    )
        ->middlewareFor('destroy', 'permission:records.delete');

    Route::resource(
        'teacher-guide-summaries',
        TeacherGuideSummaryController::class
    )
        ->except(['show'])
        ->middlewareFor('destroy', 'permission:records.delete');

    Route::resource(
        'allocation-plans',
        AllocationPlanController::class
    )
        ->middlewareFor('destroy', 'permission:records.delete');
});
