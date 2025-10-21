<?php

namespace App\Observers;

use App\Models\Obm;
use App\Models\Orcamento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon; // Carbon facade is not needed; using helper

class OrcamentoObserver
{
    /**
     * Handle the Orcamento "created" event.
     */
    public function created(Orcamento $orcamento): void
    {
        // Se o orçamento já nasce aprovado, criar a OBM imediatamente
        if ($orcamento->status === 'aprovado') {
            $this->criarObmSeNecessario($orcamento);
        }
    }

    /**
     * Handle the Orcamento "updated" event.
     */
    public function updated(Orcamento $orcamento): void
    {
        // Apenas quando o status muda para 'aprovado'
        if ($orcamento->wasChanged('status') && $orcamento->status === 'aprovado') {
            $this->criarObmSeNecessario($orcamento);
        }
    }

    /**
     * Cria a OBM para o orçamento, evitando duplicações.
     */
    private function criarObmSeNecessario(Orcamento $orcamento): void
    {
        // Evitar duplicar OBM para o mesmo orçamento
        $exists = Obm::where('orcamento_id', $orcamento->id)->exists();
        if ($exists) {
            return;
        }

        // Determinar usuário responsável (logado ou autor do orçamento)
        $userId = Auth::id() ?? $orcamento->user_id;

        // Datas padrão (hoje) para cumprir restrições não nulas
        $hoje = now()->toDateString();

        // Criar OBM mínima, referenciando apenas o orçamento
        $obm = Obm::create([
            'orcamento_id' => $orcamento->id,
            'user_id' => $userId,
            'status' => 'pendente',
            'data_inicio' => $hoje,
            'data_fim' => $hoje,
        ]);

        // Consolidar dados do orçamento para exibição em listagens
        if (method_exists($obm, 'consolidarDadosOrcamento')) {
            $obm->consolidarDadosOrcamento();
        }
    }
}