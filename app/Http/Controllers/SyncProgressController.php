<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class SyncProgressController extends Controller
{
    /**
     * Obtém o progresso atual da sincronização
     */
    public function getProgress(): JsonResponse
    {
        $progress = Cache::get('sync_progress', [
            'isRunning' => false,
            'progress' => 0,
            'currentStep' => 'Aguardando...',
            'totalRecords' => 0,
            'processedRecords' => 0,
            'stats' => [
                'clientes_criados' => 0,
                'clientes_atualizados' => 0,
                'fornecedores_criados' => 0,
                'fornecedores_atualizados' => 0,
                'erros' => 0
            ],
            'completed' => false,
            'error' => null
        ]);

        return response()->json($progress);
    }

    /**
     * Atualiza o progresso da sincronização
     */
    public function updateProgress(Request $request): JsonResponse
    {
        $data = $request->validate([
            'isRunning' => 'boolean',
            'progress' => 'integer|min:0|max:100',
            'currentStep' => 'string',
            'totalRecords' => 'integer|min:0',
            'processedRecords' => 'integer|min:0',
            'stats' => 'array',
            'completed' => 'boolean',
            'error' => 'nullable|string'
        ]);

        Cache::put('sync_progress', $data, now()->addMinutes(30));

        return response()->json(['success' => true]);
    }

    /**
     * Reseta o progresso da sincronização
     */
    public function resetProgress(): JsonResponse
    {
        Cache::forget('sync_progress');

        return response()->json(['success' => true]);
    }
}
