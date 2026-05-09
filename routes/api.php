<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – v1
|--------------------------------------------------------------------------
|
| Prefix:      /api/v1
| Auth:        Laravel Sanctum (Bearer token)
| Rate limit:  60 req/min (configurable w config/app.php)
|
*/

Route::prefix('v1')->group(function () {

    // ── Publiczne (bez tokenu) ─────────────────────────────────────────────
    Route::post('auth/token',  [AuthController::class, 'token']);

    // ── Chronione tokenem Sanctum ──────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        // Auth
        Route::delete('auth/token', [AuthController::class, 'revoke']);

        // Tickets
        Route::get('tickets',                  [TicketController::class, 'index']);
        Route::post('tickets',                 [TicketController::class, 'store']);
        Route::get('tickets/{ticket}',         [TicketController::class, 'show']);
        Route::put('tickets/{ticket}',         [TicketController::class, 'update']);
        Route::get('tickets/{ticket}/notes',   [TicketController::class, 'notes']);
        Route::post('tickets/{ticket}/notes',  [TicketController::class, 'storeNote']);

        // Companies
        Route::get('companies',                              [CompanyController::class, 'index']);
        Route::post('companies',                             [CompanyController::class, 'store']);
        Route::get('companies/{company}',                    [CompanyController::class, 'show']);
        Route::put('companies/{company}',                    [CompanyController::class, 'update']);
        Route::post('companies/{company}/domains',           [CompanyController::class, 'addDomain']);
        Route::delete('companies/{company}/domains/{domain}', [CompanyController::class, 'removeDomain']);

        // Reports (tylko admin)
        Route::middleware('role:admin')->prefix('reports')->group(function () {
            Route::get('summary',     [ReportController::class, 'summary']);
            Route::get('billing',     [ReportController::class, 'billing']);
            Route::get('sla',         [ReportController::class, 'sla']);
            Route::get('technicians', [ReportController::class, 'technicians']);
        });
    });
});
