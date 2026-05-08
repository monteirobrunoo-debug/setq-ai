<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\SetqChatController;

// Public landing — replicates the marketing screenshot
Route::get('/', fn() => view('setq.landing'))->name('landing');

// Demo chat — one route per agent. Phase 1: anonymous + 15-min sandbox.
Route::prefix('demo')->group(function () {
    Route::get('/{agent}', [SetqChatController::class, 'show'])
        ->where('agent', 'assistant|operations|growth|insights')
        ->name('demo.chat');

    Route::post('/{agent}/stream', [SetqChatController::class, 'stream'])
        ->where('agent', 'assistant|operations|growth|insights')
        ->name('demo.stream');
});

// Lead capture — buyer leaves email after enjoying the demo
Route::post('/leads', [LeadController::class, 'store'])
    ->middleware('throttle:6,1')          // anti-abuse: max 6/min/IP
    ->name('leads.store');

// Admin (single-password gate via env ADMIN_PASSWORD)
Route::prefix('admin')->middleware('admin.auth')->group(function () {
    Route::match(['GET', 'POST'], '/login',  fn() => redirect('/admin/analytics'));
    Route::get('/analytics',          [AdminController::class, 'analytics'])->name('admin.analytics');
    Route::get('/leads/{id}',         [AdminController::class, 'showLead'])
        ->whereNumber('id')
        ->name('admin.lead');
});
