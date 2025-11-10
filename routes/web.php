<?php

use App\Http\Controllers\SyncProgressController;
use App\Http\Controllers\OrcamentoPdfController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->intended('/admin')
        : redirect('/admin/login');
});

Route::middleware('auth')->group(function () {
    // Exportação de Orçamento em PDF
    Route::get('/orcamentos/{orcamento}/pdf', [OrcamentoPdfController::class, 'show'])
        ->name('orcamentos.pdf');

    // Rotas para progresso da sincronização
    Route::prefix('api/sync')->group(function () {
        Route::get('/progress', [SyncProgressController::class, 'getProgress']);
        Route::post('/progress', [SyncProgressController::class, 'updateProgress']);
        Route::delete('/progress', [SyncProgressController::class, 'resetProgress']);
    });
});
