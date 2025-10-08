<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OmieController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Omie API Integration Routes
|--------------------------------------------------------------------------
|
| Rotas para integração com a API Omie, incluindo busca de clientes,
| fornecedores e consultas detalhadas.
|
*/

Route::prefix('omie')->name('omie.')->group(function () {
    
    // Teste de conectividade (sem rate limiting restritivo)
    Route::get('test-connection', [OmieController::class, 'testConnection'])
        ->name('test-connection');
    
    // Estatísticas da integração
    Route::get('stats', [OmieController::class, 'stats'])
        ->name('stats');
    
    // Requisição direta à API Omie
    Route::post('direct-request', [OmieController::class, 'directRequest'])
        ->name('direct-request');
    
    // Clientes
    Route::prefix('clients')->name('clients.')->group(function () {
        // Busca de clientes
        Route::get('search', [OmieController::class, 'searchClients'])
            ->name('search');
        
        // Listagem com paginação
        Route::get('/', [OmieController::class, 'listClients'])
            ->name('list');
        
        // Busca por documento
        Route::get('find-by-document', [OmieController::class, 'findByDocument'])
            ->name('find-by-document');
        
        // Consulta específica
        Route::get('{omieId}', [OmieController::class, 'showClient'])
            ->name('show')
            ->where('omieId', '[0-9]+');
    });
    
    // Fornecedores
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        // Busca de fornecedores
        Route::get('search', [OmieController::class, 'searchSuppliers'])
            ->name('search');
        
        // Listagem com paginação
        Route::get('/', [OmieController::class, 'listSuppliers'])
            ->name('list');
        
        // Consulta específica
        Route::get('{omieId}', [OmieController::class, 'showSupplier'])
            ->name('show')
            ->where('omieId', '[0-9]+');
    });
    
    // Utilitários
    Route::prefix('utils')->name('utils.')->group(function () {
        // Limpar cache
        Route::delete('cache', [OmieController::class, 'clearCache'])
            ->name('clear-cache');
    });
    
    // Rota de teste simples
    Route::get('test-clients', [\App\Http\Controllers\Api\TestController::class, 'testListClients'])
        ->name('test-clients');
});

/*
|--------------------------------------------------------------------------
| Rotas de compatibilidade com sistema antigo
|--------------------------------------------------------------------------
|
| Mantém compatibilidade com o sistema antigo para facilitar migração
|
*/

Route::prefix('omie-cliente')->name('omie-cliente.')->group(function () {
    // Busca de clientes (compatibilidade)
    Route::get('search', [OmieController::class, 'searchClients'])
        ->name('search');
    
    // Busca de fornecedores (compatibilidade)
    Route::get('search-suppliers', [OmieController::class, 'searchSuppliers'])
        ->name('search-suppliers');
    
    // Consulta detalhada (compatibilidade)
    Route::get('{omieId}', function (Request $request, $omieId) {
        $tipo = $request->get('tipo', 'cliente');
        $controller = new OmieController(app(\App\Services\OmieService::class));
        
        if ($tipo === 'fornecedor') {
            return $controller->showSupplier($omieId);
        }
        
        return $controller->showClient($omieId);
    })->name('show')->where('omieId', '[0-9]+');
    
    // Limpar cache (compatibilidade)
    Route::delete('clear-cache', [OmieController::class, 'clearCache'])
        ->name('clear-cache');
});