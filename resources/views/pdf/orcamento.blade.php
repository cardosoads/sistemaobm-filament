<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Orçamento {{ $orcamento->numero_orcamento }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        .section-title { background: #f3f4f6; padding: 6px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Orçamento {{ $orcamento->numero_orcamento }}</h1>
    <p>Data: {{ $orcamento->data_orcamento?->format('d/m/Y') }}</p>
    <p>Status: {{ $orcamento->status_formatado }}</p>
    <p>Cliente: {{ $orcamento->cliente_nome }}</p>
    <p>Rota: {{ $orcamento->nome_rota }}</p>

    <div class="section-title">Resumo</div>
    <table>
        <tr>
            <th>Tipo</th>
            <td>{{ match($orcamento->tipo_orcamento) {
                'prestador' => 'Prestador',
                'aumento_km' => 'Aumento KM',
                'proprio_nova_rota' => 'Próprio - Nova Rota',
                default => $orcamento->tipo_orcamento,
            } }}</td>
        </tr>
        <tr>
            <th>Centro de Custo</th>
            <td>{{ $orcamento->centroCusto?->nome }}</td>
        </tr>
        <tr>
            <th>Usuário</th>
            <td>{{ $orcamento->user?->name }}</td>
        </tr>
        <tr>
            <th>Observações</th>
            <td>{{ $orcamento->observacoes }}</td>
        </tr>
    </table>

    @if($orcamento->tipo_orcamento === 'prestador')
        <div class="section-title">Detalhes dos Prestadores</div>
        <table>
            <thead>
                <tr>
                    <th>Fornecedor</th>
                    <th>Valor Ref.</th>
                    <th>Dias</th>
                    <th>Custo</th>
                    <th>Lucro</th>
                    <th>Impostos</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orcamento->prestadores as $p)
                    <tr>
                        <td>{{ $p->fornecedor?->razao_social ?? $p->fornecedor_nome }}</td>
                        <td>{{ number_format($p->valor_referencia, 2, ',', '.') }}</td>
                        <td>{{ $p->qtd_dias }}</td>
                        <td>{{ number_format($p->custo_fornecedor, 2, ',', '.') }}</td>
                        <td>{{ number_format($p->valor_lucro, 2, ',', '.') }}</td>
                        <td>{{ number_format($p->valor_impostos, 2, ',', '.') }}</td>
                        <td><strong>{{ number_format($p->valor_total, 2, ',', '.') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($orcamento->tipo_orcamento === 'aumento_km')
        <div class="section-title">Detalhes do Aumento KM</div>
        <table>
            <thead>
                <tr>
                    <th>KM/Dia</th>
                    <th>Dias</th>
                    <th>Combustível (km/l)</th>
                    <th>Combustível</th>
                    <th>Hora Extra</th>
                    <th>Pedágio</th>
                    <th>Total</th>
                    <th>Lucro</th>
                    <th>Impostos</th>
                    <th>Final</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orcamento->aumentosKm as $a)
                    <tr>
                        <td>{{ number_format($a->km_por_dia, 2, ',', '.') }}</td>
                        <td>{{ $a->quantidade_dias_aumento }}</td>
                        <td>{{ number_format($a->combustivel_km_litro, 2, ',', '.') }}</td>
                        <td>{{ number_format($a->valor_combustivel, 2, ',', '.') }}</td>
                        <td>{{ number_format($a->hora_extra, 2, ',', '.') }}</td>
                        <td>{{ number_format($a->pedagio, 2, ',', '.') }}</td>
                        <td>{{ number_format($a->valor_total, 2, ',', '.') }}</td>
                        <td>{{ number_format($a->valor_lucro, 2, ',', '.') }}</td>
                        <td>{{ number_format($a->valor_impostos, 2, ',', '.') }}</td>
                        <td><strong>{{ number_format($a->valor_final, 2, ',', '.') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($orcamento->tipo_orcamento === 'proprio_nova_rota')
        <div class="section-title">Detalhes da Nova Rota</div>
        <table>
            <thead>
                <tr>
                    <th>Inclui Funcionário</th>
                    <th>Inclui Frota</th>
                    <th>Inclui Fornecedor</th>
                    <th>Valor Funcionário</th>
                    <th>Valor Aluguel Frota</th>
                    <th>Fornecedor</th>
                    <th>Referência</th>
                    <th>Dias</th>
                    <th>Custo</th>
                    <th>Lucro</th>
                    <th>Impostos</th>
                    <th>Total</th>
                    <th>Final</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orcamento->propriosNovaRota as $r)
                    <tr>
                        <td>{{ $r->incluir_funcionario ? 'Sim' : 'Não' }}</td>
                        <td>{{ $r->incluir_frota ? 'Sim' : 'Não' }}</td>
                        <td>{{ $r->incluir_fornecedor ? 'Sim' : 'Não' }}</td>
                        <td>{{ number_format($r->valor_funcionario, 2, ',', '.') }}</td>
                        <td>{{ number_format($r->valor_aluguel_frota, 2, ',', '.') }}</td>
                        <td>{{ $r->fornecedor?->razao_social ?? $r->fornecedor_nome }}</td>
                        <td>{{ number_format($r->fornecedor_referencia, 2, ',', '.') }}</td>
                        <td>{{ $r->fornecedor_dias }}</td>
                        <td>{{ number_format($r->fornecedor_custo, 2, ',', '.') }}</td>
                        <td>{{ number_format($r->fornecedor_lucro, 2, ',', '.') }}</td>
                        <td>{{ number_format($r->fornecedor_impostos, 2, ',', '.') }}</td>
                        <td>{{ number_format($r->fornecedor_total, 2, ',', '.') }}</td>
                        <td><strong>{{ number_format($r->valor_final, 2, ',', '.') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="section-title">Totais</div>
    <table>
        <tr>
            <th>Valor Total</th>
            <td>{{ number_format($orcamento->valor_total, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Valor Impostos</th>
            <td>{{ number_format($orcamento->valor_impostos, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Valor Final</th>
            <td><strong>{{ number_format($orcamento->valor_final, 2, ',', '.') }}</strong></td>
        </tr>
    </table>
</body>
</html>