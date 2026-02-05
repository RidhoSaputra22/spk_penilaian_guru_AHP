<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AhpController;
use App\Http\Controllers\Admin\AssessmentController;
use App\Http\Controllers\Admin\AssessorController;
use App\Http\Controllers\Admin\CriteriaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KpiFormController;
use App\Http\Controllers\Admin\PeriodController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Assessor\AssessmentController as AssessorAssessmentController;
use App\Http\Controllers\Assessor\DashboardController as AssessorDashboardController;
// Assessor Controllers
use App\Http\Controllers\Assessor\ProfileController as AssessorProfileController;
use App\Http\Controllers\Assessor\ResultController as AssessorResultController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
// Teacher Controllers
use App\Http\Controllers\Teacher\EvidenceController as TeacherEvidenceController;
use App\Http\Controllers\Teacher\ProfileController as TeacherProfileController;
use App\Http\Controllers\Teacher\ResultController as TeacherResultController;
use App\Http\Controllers\Teacher\StatusController as TeacherStatusController;
use Illuminate\Support\Facades\Route;

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
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
// Route::middleware('guest')->group(function () {});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', UserController::class);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Period Management
    Route::resource('periods', PeriodController::class);
    Route::post('periods/{period}/status', [PeriodController::class, 'updateStatus'])->name('periods.update-status');
    Route::patch('periods/{period}/open', [PeriodController::class, 'open'])->name('periods.open');
    Route::patch('periods/{period}/close', [PeriodController::class, 'close'])->name('periods.close');
    Route::patch('periods/{period}/archive', [PeriodController::class, 'archive'])->name('periods.archive');

    // Criteria Management
    Route::get('criteria', [CriteriaController::class, 'index'])->name('criteria.index');
    Route::get('criteria/add', [CriteriaController::class, 'add'])->name('criteria.add');
    Route::post('criteria', [CriteriaController::class, 'storeSet'])->name('criteria.store');
    Route::get('criteria/sets/create', [CriteriaController::class, 'create'])->name('criteria.sets.create');
    Route::get('criteria/add', [CriteriaController::class, 'add'])->name('criteria.add');
    Route::get('criteria/sets/{criteriaSet}/edit', [CriteriaController::class, 'editSet'])->name('criteria.sets.edit');
    Route::post('criteria/sets', [CriteriaController::class, 'storeSet'])->name('criteria.store-set');
    Route::put('criteria/sets/{criteriaSet}', [CriteriaController::class, 'updateSet'])->name('criteria.update-set');
    Route::patch('criteria/sets/{criteriaSet}/lock', [CriteriaController::class, 'lockSet'])->name('criteria.sets.lock');
    Route::delete('criteria/sets/{criteriaSet}', [CriteriaController::class, 'destroySet'])->name('criteria.destroy-set');
    Route::post('criteria/nodes', [CriteriaController::class, 'storeNode'])->name('criteria.store-node');
    Route::get('criteria/{node}/edit', [CriteriaController::class, 'editNode'])->name('criteria.edit');
    Route::put('criteria/nodes/{node}', [CriteriaController::class, 'updateNode'])->name('criteria.update-node');
    Route::delete('criteria/nodes/{node}', [CriteriaController::class, 'destroyNode'])->name('criteria.destroy-node');
    Route::delete('criteria/{node}', [CriteriaController::class, 'destroyNode'])->name('criteria.destroy');
    Route::post('criteria/reorder', [CriteriaController::class, 'reorder'])->name('criteria.reorder');

    // AHP Weighting
    Route::get('ahp', [AhpController::class, 'index'])->name('ahp.index');
    Route::post('ahp/model', [AhpController::class, 'createModel'])->name('ahp.create-model');
    Route::post('ahp/comparisons', [AhpController::class, 'saveComparisons'])->name('ahp.save-comparisons');
    Route::post('ahp/{ahpModel}/comparisons', [AhpController::class, 'storeComparisons'])->name('ahp.store-comparisons');
    Route::post('ahp/{ahpModel}/regenerate', [AhpController::class, 'regenerateComparisons'])->name('ahp.regenerate-comparisons');
    Route::post('ahp/{ahpModel}/finalize', [AhpController::class, 'finalize'])->name('ahp.finalize');
    Route::post('ahp/{ahpModel}/reset', [AhpController::class, 'reset'])->name('ahp.reset');

    // KPI Form Builder
    Route::get('kpi-forms', [KpiFormController::class, 'index'])->name('kpi-forms.index');
    Route::get('kpi-forms/create', [KpiFormController::class, 'create'])->name('kpi-forms.create');
    Route::post('kpi-forms', [KpiFormController::class, 'store'])->name('kpi-forms.store');
    Route::get('kpi-forms/{template}/edit', [KpiFormController::class, 'edit'])->name('kpi-forms.edit');
    Route::put('kpi-forms/{template}', [KpiFormController::class, 'update'])->name('kpi-forms.update');
    Route::get('kpi-forms/{template}/clone', [KpiFormController::class, 'clone'])->name('kpi-forms.clone');
    Route::get('kpi-forms/{template}/versions', [KpiFormController::class, 'versions'])->name('kpi-forms.versions');
    Route::delete('kpi-forms/{template}/versions/{version}', [KpiFormController::class, 'deleteVersion'])->name('kpi-forms.delete-version');
    Route::get('kpi-forms/{template}/builder', [KpiFormController::class, 'builderSimple'])->name('kpi-forms.builder');
    Route::get('kpi-forms/{template}/preview', [KpiFormController::class, 'preview'])->name('kpi-forms.preview');
    Route::post('kpi-forms/{template}/publish', [KpiFormController::class, 'publish'])->name('kpi-forms.publish');
    Route::patch('kpi-forms/versions/{version}/publish', [KpiFormController::class, 'publishVersion'])->name('kpi-forms.publish-version');
    Route::post('kpi-forms/{template}/new-version', [KpiFormController::class, 'createNewVersion'])->name('kpi-forms.new-version');
    Route::delete('kpi-forms/{template}', [KpiFormController::class, 'destroy'])->name('kpi-forms.destroy');
    // KPI Form Builder - Section & Item Management
    Route::get('kpi-forms/versions/{version}/sections/create', [KpiFormController::class, 'createSection'])->name('kpi-forms.create-section');
    Route::post('kpi-forms/versions/{version}/sections', [KpiFormController::class, 'addSection'])->name('kpi-forms.add-section');
    Route::get('kpi-forms/sections/{section}/edit', [KpiFormController::class, 'editSection'])->name('kpi-forms.edit-section');
    Route::put('kpi-forms/sections/{section}', [KpiFormController::class, 'updateSection'])->name('kpi-forms.update-section');
    Route::delete('kpi-forms/sections/{section}', [KpiFormController::class, 'deleteSection'])->name('kpi-forms.delete-section');
    Route::get('kpi-forms/sections/{section}/items/create', [KpiFormController::class, 'createItem'])->name('kpi-forms.create-item');
    Route::post('kpi-forms/versions/{version}/items', [KpiFormController::class, 'addItem'])->name('kpi-forms.add-item');
    Route::get('kpi-forms/items/{item}/edit', [KpiFormController::class, 'editItem'])->name('kpi-forms.edit-item');
    Route::put('kpi-forms/items/{item}', [KpiFormController::class, 'updateItem'])->name('kpi-forms.update-item');
    Route::delete('kpi-forms/items/{item}', [KpiFormController::class, 'deleteItem'])->name('kpi-forms.delete-item');

    // Teachers
    Route::get('teachers', [TeacherController::class, 'index'])->name('teachers.index');

    // Assessors
    Route::get('assessors', [AssessorController::class, 'index'])->name('assessors.index');

    // Assessments
    Route::get('assessments', [AssessmentController::class, 'index'])->name('assessments.index');
    Route::get('assessments/create', [AssessmentController::class, 'create'])->name('assessments.create');
    Route::post('assessments', [AssessmentController::class, 'store'])->name('assessments.store');
    Route::get('assessments/{assessment}', [AssessmentController::class, 'show'])->name('assessments.show');
    Route::post('assessments/assign', [AssessmentController::class, 'assign'])->name('assessments.assign');

    // Results & Ranking
    Route::get('results', [ResultController::class, 'index'])->name('results.index');
    Route::get('results/export', [ResultController::class, 'export'])->name('results.export');
    Route::get('results/export-pdf', [ResultController::class, 'export'])->name('results.export-pdf');
    Route::get('results/export-excel', [ResultController::class, 'export'])->name('results.export-excel');
    Route::post('results/calculate', [ResultController::class, 'calculate'])->name('results.calculate');
    Route::get('results/{result}', [ResultController::class, 'show'])->name('results.show');

    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Placeholder routes for views
    // Scoring Scales
    Route::resource('scoring-scales', \App\Http\Controllers\Admin\ScoringScaleController::class);

    // KPI Assignments
    Route::resource('kpi-assignments', \App\Http\Controllers\Admin\KpiAssignmentController::class)->except(['edit', 'update']);

    // Reports
    Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/generate', [\App\Http\Controllers\Admin\ReportController::class, 'generate'])->name('reports.generate');
    Route::post('reports', [\App\Http\Controllers\Admin\ReportController::class, 'store'])->name('reports.store');
    Route::get('reports/export-progress', [\App\Http\Controllers\Admin\ReportController::class, 'exportProgress'])->name('reports.export-progress');
    Route::get('reports/export-ahp', [\App\Http\Controllers\Admin\ReportController::class, 'exportAhp'])->name('reports.export-ahp');

    // Profile
    Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::delete('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'destroy'])->name('profile.delete');

    // Settings
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('settings/institution', [\App\Http\Controllers\Admin\SettingController::class, 'updateInstitution'])->name('settings.update-institution');
    Route::put('settings/scoring-scale', [\App\Http\Controllers\Admin\SettingController::class, 'updateScoringScale'])->name('settings.update-scoring-scale');
    Route::post('settings/teacher-groups', [\App\Http\Controllers\Admin\SettingController::class, 'storeTeacherGroup'])->name('settings.store-teacher-group');
    Route::put('settings/teacher-groups/{teacherGroup}', [\App\Http\Controllers\Admin\SettingController::class, 'updateTeacherGroup'])->name('settings.update-teacher-group');
    Route::delete('settings/teacher-groups/{teacherGroup}', [\App\Http\Controllers\Admin\SettingController::class, 'deleteTeacherGroup'])->name('settings.delete-teacher-group');
    Route::put('settings/ahp', [\App\Http\Controllers\Admin\SettingController::class, 'updateAhpSettings'])->name('settings.update-ahp');
    Route::put('settings/email', [\App\Http\Controllers\Admin\SettingController::class, 'updateEmailSettings'])->name('settings.update-email');
    Route::put('settings/notifications', [\App\Http\Controllers\Admin\SettingController::class, 'updateNotificationSettings'])->name('settings.update-notifications');
    Route::post('settings/backup', [\App\Http\Controllers\Admin\SettingController::class, 'createBackup'])->name('settings.create-backup');
});

/*
|--------------------------------------------------------------------------
| Assessor Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:assessor'])->prefix('assessor')->name('assessor.')->group(function () {
    // Dashboard
    Route::get('/', [AssessorDashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('profile', [AssessorProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [AssessorProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [AssessorProfileController::class, 'updatePassword'])->name('profile.update-password');

    // Assessments
    Route::get('assessments', [AssessorAssessmentController::class, 'index'])->name('assessments.index');
    Route::get('assessments/period/{period}', [AssessorAssessmentController::class, 'period'])->name('assessments.period');
    Route::get('assessments/period/{period}/teacher/{teacher}', [AssessorAssessmentController::class, 'score'])->name('assessments.score');
    Route::post('assessments/{assessment}/save-draft', [AssessorAssessmentController::class, 'saveDraft'])->name('assessments.save-draft');
    Route::post('assessments/{assessment}/submit', [AssessorAssessmentController::class, 'submit'])->name('assessments.submit');

    // Results
    Route::get('results', [AssessorResultController::class, 'index'])->name('results.index');
    Route::get('results/{assessment}', [AssessorResultController::class, 'show'])->name('results.show');
});

/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {

    // Dashboard
    Route::get('/', [TeacherDashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('profile', [TeacherProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [TeacherProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [TeacherProfileController::class, 'updatePassword'])->name('profile.update-password');

    // Assessment Status
    Route::get('status', [TeacherStatusController::class, 'index'])->name('status.index');
    Route::get('status/{assessment}', [TeacherStatusController::class, 'show'])->name('status.show');

    // Evidence Upload
    Route::get('evidence', [TeacherEvidenceController::class, 'index'])->name('evidence.index');
    Route::post('evidence/{assessment}/{item}', [TeacherEvidenceController::class, 'upload'])->name('evidence.upload');
    Route::delete('evidence/{evidence}', [TeacherEvidenceController::class, 'destroy'])->name('evidence.destroy');
    Route::get('evidence/{evidence}/download', [TeacherEvidenceController::class, 'download'])->name('evidence.download');

    // Results
    Route::get('results', [TeacherResultController::class, 'index'])->name('results.index');
    Route::get('results/{result}', [TeacherResultController::class, 'show'])->name('results.show');
    Route::get('results/{result}/download', [TeacherResultController::class, 'download'])->name('results.download');
});
