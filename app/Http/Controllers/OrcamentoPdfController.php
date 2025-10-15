<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use Barryvdh\DomPDF\Facade\Pdf;

class OrcamentoPdfController extends Controller
{
    public function show(Orcamento $orcamento)
    {
        // Carrega relacionamentos necessários para o PDF
        $orcamento->load([
            'prestadores.fornecedor',
            'prestadores.grupoImposto',
            'aumentosKm.grupoImposto',
            'propriosNovaRota.fornecedor',
            'centroCusto',
            'user'
        ]);

        $pdf = Pdf::loadView('pdf.orcamento', [
            'orcamento' => $orcamento,
        ])->setPaper('a4');

        $fileName = 'orcamento-' . ($orcamento->numero_orcamento ?? $orcamento->id) . '.pdf';

        return $pdf->download($fileName);
    }
}