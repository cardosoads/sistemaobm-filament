<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SyncProgressController;

Route::get('/', function () {
    return redirect('/admin');
});

// Rotas para progresso da sincronização
Route::prefix('api/sync')->group(function () {
    Route::get('/progress', [SyncProgressController::class, 'getProgress']);
    Route::post('/progress', [SyncProgressController::class, 'updateProgress']);
    Route::delete('/progress', [SyncProgressController::class, 'resetProgress']);
});
