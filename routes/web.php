<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CriterionController;
use App\Http\Controllers\GeraiController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PenjelasanFormulirController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware('auth');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/user', [UserController::class, 'index'])->middleware('auth');
Route::get('/user/create', [UserController::class, 'create'])->middleware('auth');
Route::post('/user', [UserController::class, 'store'])->middleware('auth');
Route::get('/user/{id}/edit', [UserController::class, 'edit'])->middleware('auth');
Route::put('/user/{id}', [UserController::class, 'updateUser'])->middleware('auth');
Route::delete('/user/{id}', [UserController::class, 'destroy'])->middleware('auth');
Route::put('/user', [UserController::class, 'update'])->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/create', [CategoryController::class, 'create']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/reorder', [CategoryController::class, 'reorder']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::get('/tugas/penjelasan-formulir-2', [PenjelasanFormulirController::class, 'index'])->defaults('formulir', 2);
    Route::get('/tugas/penjelasan-formulir-3', [PenjelasanFormulirController::class, 'index'])->defaults('formulir', 3);
    Route::post('/tugas/penjelasan-formulir/{formulir}', [PenjelasanFormulirController::class, 'store']);
    Route::put('/tugas/penjelasan-formulir/{penjelasan_formulir}', [PenjelasanFormulirController::class, 'update']);
    Route::delete('/tugas/penjelasan-formulir/{penjelasan_formulir}', [PenjelasanFormulirController::class, 'destroy']);
    Route::get('/tugas/penjelasan-formulir/{formulir}/import', [PenjelasanFormulirController::class, 'importForm']);
    Route::post('/tugas/penjelasan-formulir/{formulir}/import', [PenjelasanFormulirController::class, 'import']);
    Route::get('/tugas/penjelasan-formulir/{formulir}/template', [PenjelasanFormulirController::class, 'template']);

    Route::get('/categories/{category}/items/create', [ItemController::class, 'create']);
    Route::post('/categories/{category}/items', [ItemController::class, 'store']);
    Route::get('/items/{item}/edit', [ItemController::class, 'edit']);
    Route::put('/items/{item}', [ItemController::class, 'update']);
    Route::put('/items/{item}/up', [ItemController::class, 'moveUp']);
    Route::put('/items/{item}/down', [ItemController::class, 'moveDown']);
    Route::delete('/items/{item}', [ItemController::class, 'destroy']);

    Route::put('/categories/{category}/items/reorder', [ItemController::class, 'reorder']);
    Route::put('/categories/{category}/items/bobot', [ItemController::class, 'batchBobot']);

    Route::get('/items/{item}/criteria', [CriterionController::class, 'index']);
    Route::get('/items/{item}/criteria/create', [CriterionController::class, 'create']);
    Route::post('/items/{item}/criteria', [CriterionController::class, 'store']);
    Route::post('/items/{item}/criteria/batch', [CriterionController::class, 'batchStore']);
    Route::put('/items/{item}/criteria/reorder', [CriterionController::class, 'reorder']);
    Route::get('/items/{item}/criteria/{criterion}/edit', [CriterionController::class, 'edit']);
    Route::put('/items/{item}/criteria/{criterion}', [CriterionController::class, 'update']);
    Route::delete('/items/{item}/criteria/{criterion}', [CriterionController::class, 'destroy']);

    // Gerai
    Route::get('/gerais', [GeraiController::class, 'index']);
    Route::get('/gerais/create', [GeraiController::class, 'create']);
    Route::get('/gerais/import', [GeraiController::class, 'importForm']);
    Route::post('/gerais/import', [GeraiController::class, 'importExcel']);
    Route::get('/gerais/export', [GeraiController::class, 'exportExcel']);
    Route::get('/gerais/template', [GeraiController::class, 'template']);
    Route::post('/gerais', [GeraiController::class, 'store']);
    Route::get('/gerais/{gerai}', [GeraiController::class, 'show']);
    Route::get('/gerais/{gerai}/edit', [GeraiController::class, 'edit']);
    Route::put('/gerais/{gerai}', [GeraiController::class, 'update']);
    Route::delete('/gerais/{gerai}', [GeraiController::class, 'destroy']);

    Route::get('/ranking', [\App\Http\Controllers\RankingController::class, 'index']);
    Route::get('/ranking/pra-monitoring', [\App\Http\Controllers\RankingController::class, 'praMonitoring']);
    Route::get('/ranking/peringkat', [\App\Http\Controllers\RankingController::class, 'peringkat']);
    Route::get('/ranking/peringkat/excel', [\App\Http\Controllers\RankingController::class, 'peringkatExcel']);
    Route::get('/ranking/excel', [\App\Http\Controllers\RankingController::class, 'excel']);
    Route::get('/ranking/performa', [\App\Http\Controllers\RankingController::class, 'performa']);
    Route::get('/ranking/import', [\App\Http\Controllers\RankingController::class, 'importForm']);
    Route::post('/ranking/import', [\App\Http\Controllers\RankingController::class, 'import']);
    Route::get('/ranking/import/template', [\App\Http\Controllers\RankingController::class, 'template']);

    Route::get('/report', [ReportController::class, 'index']);
    Route::get('/report/pre-monitoring', [ReportController::class, 'preMonitoring']);
    Route::get('/report/pdf', [ReportController::class, 'pdf']);
    Route::get('/report/excel', [ReportController::class, 'excel']);
    Route::get('/report/analytics', [ReportController::class, 'analytics']);
    Route::get('/report/analytics/excel', [ReportController::class, 'analyticsExcel']);
    Route::get('/report/ambil-data', [ReportController::class, 'ambilData']);
    Route::get('/report/checklist-tidak-sempurna', [ReportController::class, 'checklistTidakSempurna']);
    Route::get('/report/export-all-excel', [ReportController::class, 'exportAllExcel']);
    // Semester Periods
    Route::get('/semester-periods', [\App\Http\Controllers\SemesterPeriodController::class, 'index']);
    Route::get('/semester-periods/create', [\App\Http\Controllers\SemesterPeriodController::class, 'create']);
    Route::post('/semester-periods', [\App\Http\Controllers\SemesterPeriodController::class, 'store']);
    Route::get('/semester-periods/{semesterPeriod}/edit', [\App\Http\Controllers\SemesterPeriodController::class, 'edit']);
    Route::put('/semester-periods/{semesterPeriod}', [\App\Http\Controllers\SemesterPeriodController::class, 'update']);
    Route::delete('/semester-periods/{semesterPeriod}', [\App\Http\Controllers\SemesterPeriodController::class, 'destroy']);

    Route::get('/import', [ImportController::class, 'create']);
    Route::post('/import', [ImportController::class, 'import']);
    Route::get('/import/template', [ImportController::class, 'template']);

    // Monitoring
    Route::get('/monitoring', [\App\Http\Controllers\MonitoringController::class, 'selectGerai']);
    Route::get('/monitoring/checkin/{gerai}', [\App\Http\Controllers\MonitoringController::class, 'checkinForm']);
    Route::post('/monitoring/checkin/{gerai}', [\App\Http\Controllers\MonitoringController::class, 'doCheckin']);
    Route::get('/monitoring/{report}/assessment', [\App\Http\Controllers\MonitoringController::class, 'assessment']);
    Route::get('/monitoring/{report}/assessment/{category}/form', [\App\Http\Controllers\MonitoringController::class, 'assessmentForm']);
    Route::post('/monitoring/{report}/assessment/{category}/form', [\App\Http\Controllers\MonitoringController::class, 'saveAssessmentForm']);
    Route::post('/monitoring/{report}/submit', [\App\Http\Controllers\MonitoringController::class, 'submit']);
    Route::post('/monitoring/{report}/cancel', [\App\Http\Controllers\MonitoringController::class, 'cancelAssessment']);
    Route::get('/monitoring/{report}/temuan', [\App\Http\Controllers\MonitoringController::class, 'temuanForm']);
    Route::post('/monitoring/{report}/temuan', [\App\Http\Controllers\MonitoringController::class, 'saveTemuan']);
    Route::get('/monitoring/{report}/pdf', [\App\Http\Controllers\MonitoringController::class, 'pdf']);
    Route::get('/monitoring/{report}/excel', [\App\Http\Controllers\MonitoringController::class, 'excel']);
    Route::get('/monitoring/{report}', [\App\Http\Controllers\MonitoringController::class, 'show']);
    Route::delete('/monitoring/{report}', [\App\Http\Controllers\MonitoringController::class, 'destroy']);

    // Pra-Monitoring
    Route::get('/pra-monitoring', [\App\Http\Controllers\PraMonitoringController::class, 'selectGerai']);
    Route::get('/pra-monitoring/checkin/{gerai}', [\App\Http\Controllers\PraMonitoringController::class, 'checkinForm']);
    Route::post('/pra-monitoring/checkin/{gerai}', [\App\Http\Controllers\PraMonitoringController::class, 'doCheckin']);
    Route::get('/pra-monitoring/{report}/assessment', [\App\Http\Controllers\PraMonitoringController::class, 'assessment']);
    Route::get('/pra-monitoring/{report}/assessment/{category}/form', [\App\Http\Controllers\PraMonitoringController::class, 'assessmentForm']);
    Route::post('/pra-monitoring/{report}/assessment/{category}/form', [\App\Http\Controllers\PraMonitoringController::class, 'saveAssessmentForm']);
    Route::post('/pra-monitoring/{report}/submit', [\App\Http\Controllers\PraMonitoringController::class, 'submit']);
    Route::post('/pra-monitoring/{report}/cancel', [\App\Http\Controllers\PraMonitoringController::class, 'cancelAssessment']);
    Route::get('/pra-monitoring/{report}/temuan', [\App\Http\Controllers\PraMonitoringController::class, 'temuanForm']);
    Route::post('/pra-monitoring/{report}/temuan', [\App\Http\Controllers\PraMonitoringController::class, 'saveTemuan']);
    Route::get('/pra-monitoring/{report}/pdf', [\App\Http\Controllers\PraMonitoringController::class, 'pdf']);
    Route::get('/pra-monitoring/{report}/excel', [\App\Http\Controllers\PraMonitoringController::class, 'excel']);
    Route::get('/pra-monitoring/{report}', [\App\Http\Controllers\PraMonitoringController::class, 'show']);
    Route::delete('/pra-monitoring/{report}', [\App\Http\Controllers\PraMonitoringController::class, 'destroy']);

    // Excel Templates
    Route::get('/excel-template', function () {
        return view('excel-template');
    });
    Route::get('/excel-template/example', [\App\Http\Controllers\MonitoringController::class, 'downloadExampleTemplate']);
    Route::post('/excel-template/upload', [\App\Http\Controllers\MonitoringController::class, 'uploadTemplate']);
    Route::delete('/excel-template/delete', [\App\Http\Controllers\MonitoringController::class, 'deleteTemplate']);
});
