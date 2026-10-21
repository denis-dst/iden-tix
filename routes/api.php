<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SuperadminController;
use App\Http\Controllers\Api\EventProviderController;
use App\Http\Controllers\Api\POSController;
use App\Http\Controllers\Api\GateController;

use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * Superadmin Routes
 */
Route::middleware(['auth:sanctum', 'role:Superadmin'])->group(function () {
    Route::get('/superadmin/tenants', [SuperadminController::class, 'listTenants']);
    Route::post('/superadmin/tenants', [SuperadminController::class, 'createTenant']);
    Route::patch('/superadmin/tenants/{tenant}/status', [SuperadminController::class, 'updateTenantStatus']);
    Route::get('/superadmin/infra/health', [SuperadminController::class, 'getInfrasHealth']);
    Route::post('/superadmin/transactions/{transaction}/override', [SuperadminController::class, 'overrideTransaction']);
});

/**
 * Shared Tenant Routes (Events List)
 */
Route::middleware(['auth:sanctum', 'role:Superadmin|Penyedia Event|Petugas Loket|Petugas Gate'])->group(function () {
    Route::get('/events', [EventProviderController::class, 'listEvents']);
});

/**
 * Event Provider & POS Routes
 */
Route::middleware(['auth:sanctum', 'role:Superadmin|Penyedia Event|Petugas Loket|Petugas Gate'])->group(function () {
    Route::post('/events', [EventProviderController::class, 'storeEvent']);
    Route::post('/events/{event}/ticket-categories', [EventProviderController::class, 'storeTicketCategory']);
    Route::post('/ticket-categories/{category}/update-design', [EventProviderController::class, 'updateTicketDesign']);
    Route::get('/events/{event}/analytics', [EventProviderController::class, 'getAnalytics']);
    
    // Penjualan & Redemption
    Route::post('/pos/events/{event}/sell', [POSController::class, 'sellTicket']);
    Route::get('/pos/check-ticket/{code}', [POSController::class, 'checkTicket']);
    Route::post('/pos/redeem', [POSController::class, 'redeemTicket']);
});

/**
 * Gate (Petugas Gate) Routes
 */
Route::middleware(['auth:sanctum', 'role:Superadmin|Penyedia Event|Petugas Gate|Petugas Loket'])->group(function () {
    Route::get('/gate/list', [GateController::class, 'listGates']);
    // Download wristband data untuk mode offline/local
    Route::get('/gate/download-data', [GateController::class, 'downloadData']);
    // Scan QR / Wristband
    Route::post('/gate/scan', [GateController::class, 'scan']);
    // Bulk Check-in/Check-out
    Route::post('/gate/bulk-checkin', [GateController::class, 'bulkCheckin']);
    // Upload scan logs ke server (background sync)
    Route::post('/gate/sync', [GateController::class, 'syncLogs']);
    // Alias upload-data → syncLogs (digunakan oleh tombol "Upload Data" di app)
    Route::post('/gate/upload-data', [GateController::class, 'syncLogs']);
});

/**
 * Public Webhook Endpoints (WAGO / Payment Gateways)
 */
Route::any('/wago/notification', [App\Http\Controllers\PublicEventController::class, 'handleWagoNotification']);
Route::any('/wago-webhook', [App\Http\Controllers\PublicEventController::class, 'handleWagoNotification']);
Route::any('/wago/webhook', [App\Http\Controllers\PublicEventController::class, 'handleWagoNotification']);
Route::any('/ipaymu/notification', [App\Http\Controllers\PublicEventController::class, 'handleIPaymuNotification']);
