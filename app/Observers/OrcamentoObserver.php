<?php

namespace App\Observers;

use App\Models\Obm;
use App\Models\Orcamento;
use Illuminate\Support\Facades\Auth;

class OrcamentoObserver
{
    /**
     * Handle the Orcamento "updated" event.
     */
    public function updated(Orcamento $orcamento): void
    {
        // Apenas quando o status muda para 'aprovado'
        if ($orcamento->wasChanged('status') && $orcamento->status === 'aprovado') {
            // Evitar duplicar OBM para o mesmo orçamento
            $exists = Obm::where('orcamento_id', $orcamento->id)->exists();
            if ($exists) {
                return;
            }

            // Determinar usuário responsável (logado ou autor do orçamento)
            $userId = Auth::id() ?? $orcamento->user_id;

            // Criar OBM mínima, referenciando apenas o orçamento
            Obm::create([
                'orcamento_id' => $orcamento->id,
                'user_id' => $userId,
                'status' => 'pendente',
                // Datas ficam nulas inicialmente se a tabela permitir; serão definidas posteriormente na operação.
            ]);
        }
    }
}