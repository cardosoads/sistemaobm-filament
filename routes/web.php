<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SyncProgressController;
use App\Http\Controllers\OrcamentoPdfController;

Route::get('/', function () {
    return view('welcome');
});

// Exportação de Orçamento em PDF (protegida por autenticação)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/orcamentos/{orcamento}/pdf', [OrcamentoPdfController::class, 'show'])
        ->name('orcamentos.pdf');
});

// Rotas para progresso da sincronização
Route::prefix('api/sync')->group(function () {
    Route::get('/progress', [SyncProgressController::class, 'getProgress']);
    Route::post('/progress', [SyncProgressController::class, 'updateProgress']);
    Route::delete('/progress', [SyncProgressController::class, 'resetProgress']);
});
