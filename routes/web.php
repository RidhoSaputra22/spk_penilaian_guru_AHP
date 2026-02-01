<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PeriodController;
use App\Http\Controllers\Admin\CriteriaController;
use App\Http\Controllers\Admin\AhpController;
use App\Http\Controllers\Admin\KpiFormController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\AssessorController;
use App\Http\Controllers\Admin\AssessmentController;
use App\Http\Controllers\Admin\ActivityLogController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', UserController::class);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Period Management
    Route::resource('periods', PeriodController::class);
    Route::post('periods/{period}/status', [PeriodController::class, 'updateStatus'])->name('periods.update-status');

    // Criteria Management
    Route::get('criteria', [CriteriaController::class, 'index'])->name('criteria.index');
    Route::post('criteria/sets', [CriteriaController::class, 'storeSet'])->name('criteria.store-set');
    Route::put('criteria/sets/{criteriaSet}', [CriteriaController::class, 'updateSet'])->name('criteria.update-set');
    Route::delete('criteria/sets/{criteriaSet}', [CriteriaController::class, 'destroySet'])->name('criteria.destroy-set');
    Route::post('criteria/nodes', [CriteriaController::class, 'storeNode'])->name('criteria.store-node');
    Route::put('criteria/nodes/{node}', [CriteriaController::class, 'updateNode'])->name('criteria.update-node');
    Route::delete('criteria/nodes/{node}', [CriteriaController::class, 'destroyNode'])->name('criteria.destroy-node');
    Route::post('criteria/reorder', [CriteriaController::class, 'reorder'])->name('criteria.reorder');

    // AHP Weighting
    Route::get('ahp', [AhpController::class, 'index'])->name('ahp.index');
    Route::post('ahp/model', [AhpController::class, 'createModel'])->name('ahp.create-model');
    Route::post('ahp/comparisons', [AhpController::class, 'saveComparisons'])->name('ahp.save-comparisons');
    Route::post('ahp/{ahpModel}/finalize', [AhpController::class, 'finalize'])->name('ahp.finalize');
    Route::post('ahp/{ahpModel}/reset', [AhpController::class, 'reset'])->name('ahp.reset');

    // KPI Form Builder
    Route::get('kpi-forms', [KpiFormController::class, 'index'])->name('kpi-forms.index');
    Route::get('kpi-forms/create', [KpiFormController::class, 'create'])->name('kpi-forms.create');
    Route::post('kpi-forms', [KpiFormController::class, 'store'])->name('kpi-forms.store');
    Route::get('kpi-forms/{template}/builder', [KpiFormController::class, 'builder'])->name('kpi-forms.builder');
    Route::post('kpi-forms/{template}/builder', [KpiFormController::class, 'saveBuilder'])->name('kpi-forms.save-builder');
    Route::get('kpi-forms/{template}/preview', [KpiFormController::class, 'preview'])->name('kpi-forms.preview');
    Route::post('kpi-forms/{template}/publish', [KpiFormController::class, 'publish'])->name('kpi-forms.publish');
    Route::post('kpi-forms/{template}/new-version', [KpiFormController::class, 'createNewVersion'])->name('kpi-forms.new-version');
    Route::delete('kpi-forms/{template}', [KpiFormController::class, 'destroy'])->name('kpi-forms.destroy');

    // Teachers
    Route::get('teachers', [TeacherController::class, 'index'])->name('teachers.index');

    // Assessors
    Route::get('assessors', [AssessorController::class, 'index'])->name('assessors.index');

    // Assessments
    Route::get('assessments', [AssessmentController::class, 'index'])->name('assessments.index');
    Route::get('assessments/{assessment}', [AssessmentController::class, 'show'])->name('assessments.show');
    Route::post('assessments/assign', [AssessmentController::class, 'assign'])->name('assessments.assign');

    // Results & Ranking
    Route::get('results', [ResultController::class, 'index'])->name('results.index');
    Route::get('results/export', [ResultController::class, 'export'])->name('results.export');
    Route::post('results/calculate', [ResultController::class, 'calculate'])->name('results.calculate');
    Route::get('results/{result}', [ResultController::class, 'show'])->name('results.show');

    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Profile (placeholder)
    Route::get('profile', function() {
        return view('admin.profile.edit');
    })->name('profile.edit');

    // Settings (placeholder)
    Route::get('settings', function() {
        return view('admin.settings.index');
    })->name('settings.index');
});

/*
|--------------------------------------------------------------------------
| Assessor Routes (placeholder)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('assessor')->name('assessor.')->group(function () {
    Route::get('/', function() {
        return view('assessor.dashboard');
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Teacher Routes (placeholder)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/', function() {
        return view('teacher.dashboard');
    })->name('dashboard');
});
