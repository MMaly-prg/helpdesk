<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\TicketWebController;
use App\Http\Controllers\Web\CompanyWebController;
use App\Http\Controllers\Web\ReportWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes – Panel serwisanta
|--------------------------------------------------------------------------
*/

// ── Auth ──────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Panel (wymaga logowania) ───────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Tickets
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/',           [TicketWebController::class, 'index'])->name('index');
        Route::get('/create',     [TicketWebController::class, 'create'])->name('create');
        Route::post('/',          [TicketWebController::class, 'store'])->name('store');
        Route::get('/{ticket}',   [TicketWebController::class, 'show'])->name('show');
        Route::post('/{ticket}/notes', [TicketWebController::class, 'storeNote'])->name('notes.store');
        Route::post('/{ticket}/assign', [TicketWebController::class, 'assign'])->name('assign');
        Route::post('/{ticket}/status', [TicketWebController::class, 'changeStatus'])->name('status');
        Route::post('/{ticket}/attachments', [TicketWebController::class, 'uploadAttachment'])->name('attachments.store');
    });

    // Companies (tylko admin)
    Route::middleware('role:admin')->prefix('companies')->name('companies.')->group(function () {
        Route::get('/',              [CompanyWebController::class, 'index'])->name('index');
        Route::get('/create',        [CompanyWebController::class, 'create'])->name('create');
        Route::post('/',             [CompanyWebController::class, 'store'])->name('store');
        Route::get('/{company}',     [CompanyWebController::class, 'show'])->name('show');
        Route::get('/{company}/edit',[CompanyWebController::class, 'edit'])->name('edit');
        Route::put('/{company}',     [CompanyWebController::class, 'update'])->name('update');
    });

    // Reports
    Route::middleware('role:admin')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/',           [ReportWebController::class, 'index'])->name('index');
        Route::get('/billing',    [ReportWebController::class, 'billing'])->name('billing');
        Route::get('/export/pdf', [ReportWebController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/csv', [ReportWebController::class, 'exportCsv'])->name('export.csv');
    });
});
