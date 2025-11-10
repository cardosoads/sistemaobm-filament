<?php

use App\Http\Controllers\SyncProgressController;
use App\Http\Controllers\OrcamentoPdfController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

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
