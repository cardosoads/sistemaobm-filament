<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Orçamento {{ $orcamento->numero_orcamento }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.4; color: #000; }
        .container { max-width: 1000px; margin: 0 auto; padding: 15px; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #ccc; }
        .header h1 { font-size: 16px; font-weight: bold; margin: 0 0 5px 0; }
        .header .subtitle { font-size: 12px; color: #666; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; font-size: 13px; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid #eee; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 15px; }
        .info-item { margin-bottom: 8px; }
        .info-label { font-weight: 600; font-size: 11px; color: #444; margin-bottom: 2px; }
        .info-value { font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 11px; }
        th { background: #f5f5f5; padding: 8px; text-align: left; font-weight: 600; border: 1px solid #ddd; }
        td { padding: 8px; border: 1px solid #ddd; }
        .total-section { margin-top: 20px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; }
        .total-row { display: flex; justify-content: space-between; padding: 5px 0; }
        .total-label { font-weight: 600; }
        .total-value { font-weight: bold; }
        .total-final { font-size: 14px; font-weight: bold; color: #000; }
        .text-right { text-align: right; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ORÇAMENTO {{ $orcamento->numero_orcamento }}</h1>
            <div class="subtitle">Sistema OBM - Documento de Controle</div>
        </div>

        <div class="section">
            <div class="section-title">INFORMAÇÕES BÁSICAS</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Data do Orçamento</div>
                    <div class="info-value">{{ $orcamento->data_orcamento?->format('d/m/Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">{{ $orcamento->status_formatado }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Cliente</div>
                    <div class="info-value">{{ $orcamento->cliente_nome }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Rota</div>
                    <div class="info-value">{{ $orcamento->nome_rota }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tipo de Orçamento</div>
                    <div class="info-value">
                        {{ match($orcamento->tipo_orcamento) {
                            'prestador' => 'Prestador',
                            'aumento_km' => 'Aumento KM',
                            'proprio_nova_rota' => 'Próprio - Nova Rota',
                            default => $orcamento->tipo_orcamento,
                        } }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Centro de Custo</div>
                    <div class="info-value">{{ $orcamento->centroCusto?->nome ?? 'Não informado' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Usuário Responsável</div>
                    <div class="info-value">{{ $orcamento->user?->name ?? 'Não informado' }}</div>
                </div>
            </div>
        </div>

        @if($orcamento->observacoes)
            <div class="section">
                <div class="section-title">OBSERVAÇÕES</div>
                <div class="info-value">{{ $orcamento->observacoes }}</div>
            </div>
        @endif

    @if($orcamento->tipo_orcamento === 'prestador')
        <div class="section-title">Detalhes dos Prestadores</div>
        @foreach($orcamento->prestadores as $index => $p)
            <div class="prestador-section" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9;">
                <div class="section-title" style="font-size: 12px; margin-bottom: 10px;">Prestador {{ $index + 1 }}</div>
                
                <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-bottom: 10px;">
                    <div class="info-item">
                        <div class="info-label">Fornecedor</div>
                        <div class="info-value">{{ $p->fornecedor?->razao_social ?? $p->fornecedor_nome }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Valor Referência</div>
                        <div class="info-value">R$ {{ number_format($p->valor_referencia, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Dias</div>
                        <div class="info-value">{{ $p->qtd_dias }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Custo Fornecedor</div>
                        <div class="info-value">R$ {{ number_format($p->custo_fornecedor, 2, ',', '.') }}</div>
                    </div>
                </div>
                
                <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">
                    <div class="info-item">
                        <div class="info-label">Lucro</div>
                        <div class="info-value">R$ {{ number_format($p->valor_lucro, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Impostos</div>
                        <div class="info-value">R$ {{ number_format($p->valor_impostos, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total</div>
                        <div class="info-value" style="font-weight: bold;">R$ {{ number_format($p->valor_total, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    @if($orcamento->tipo_orcamento === 'aumento_km')
        <div class="section-title">Detalhes do Aumento KM</div>
        @foreach($orcamento->aumentosKm as $index => $a)
            <div class="aumento-section" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9;">
                <div class="section-title" style="font-size: 12px; margin-bottom: 10px;">Aumento KM {{ $index + 1 }}</div>
                
                <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-bottom: 10px;">
                    <div class="info-item">
                        <div class="info-label">KM/Dia</div>
                        <div class="info-value">{{ number_format($a->km_por_dia, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Dias</div>
                        <div class="info-value">{{ $a->quantidade_dias_aumento }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Combustível (km/l)</div>
                        <div class="info-value">{{ number_format($a->combustivel_km_litro, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Combustível</div>
                        <div class="info-value">R$ {{ number_format($a->valor_combustivel, 2, ',', '.') }}</div>
                    </div>
                </div>
                
                <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-bottom: 10px;">
                    <div class="info-item">
                        <div class="info-label">Hora Extra</div>
                        <div class="info-value">R$ {{ number_format($a->hora_extra, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Pedágio</div>
                        <div class="info-value">R$ {{ number_format($a->pedagio, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total</div>
                        <div class="info-value">R$ {{ number_format($a->valor_total, 2, ',', '.') }}</div>
                    </div>
                </div>
                
                <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px;">
                    <div class="info-item">
                        <div class="info-label">Lucro</div>
                        <div class="info-value">R$ {{ number_format($a->valor_lucro, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Impostos</div>
                        <div class="info-value">R$ {{ number_format($a->valor_impostos, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Valor Final</div>
                        <div class="info-value" style="font-weight: bold;">R$ {{ number_format($a->valor_final, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    @if($orcamento->tipo_orcamento === 'proprio_nova_rota')
        <div class="section-title">Detalhes da Nova Rota</div>
        @foreach($orcamento->propriosNovaRota as $index => $r)
            <div class="nova-rota-section" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9;">
                <div class="section-title" style="font-size: 12px; margin-bottom: 10px;">Nova Rota {{ $index + 1 }}</div>
                
                <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-bottom: 10px;">
                    <div class="info-item">
                        <div class="info-label">Inclui Funcionário</div>
                        <div class="info-value">{{ $r->incluir_funcionario ? 'Sim' : 'Não' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Inclui Frota</div>
                        <div class="info-value">{{ $r->incluir_frota ? 'Sim' : 'Não' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Inclui Fornecedor</div>
                        <div class="info-value">{{ $r->incluir_fornecedor ? 'Sim' : 'Não' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Valor Funcionário</div>
                        <div class="info-value">R$ {{ number_format($r->valor_funcionario, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Valor Aluguel Frota</div>
                        <div class="info-value">R$ {{ number_format($r->valor_aluguel_frota, 2, ',', '.') }}</div>
                    </div>
                </div>
                
                <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-bottom: 10px;">
                    <div class="info-item">
                        <div class="info-label">Fornecedor</div>
                        <div class="info-value">{{ $r->fornecedor?->razao_social ?? $r->fornecedor_nome }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Referência</div>
                        <div class="info-value">R$ {{ number_format($r->fornecedor_referencia, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Dias</div>
                        <div class="info-value">{{ $r->fornecedor_dias }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Custo</div>
                        <div class="info-value">R$ {{ number_format($r->fornecedor_custo, 2, ',', '.') }}</div>
                    </div>
                </div>
                
                <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px;">
                    <div class="info-item">
                        <div class="info-label">Lucro</div>
                        <div class="info-value">R$ {{ number_format($r->fornecedor_lucro, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Impostos</div>
                        <div class="info-value">R$ {{ number_format($r->fornecedor_impostos, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Total</div>
                        <div class="info-value">R$ {{ number_format($r->fornecedor_total, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Valor Final</div>
                        <div class="info-value" style="font-weight: bold;">R$ {{ number_format($r->valor_final, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        @endforeach
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