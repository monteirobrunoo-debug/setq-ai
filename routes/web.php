<?php

use Illuminate\Support\Facades\Route;
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
