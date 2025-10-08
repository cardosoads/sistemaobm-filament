<?php

namespace App\Services;

use App\Models\CentroCusto;
use App\Services\OmieService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CentroCustoSyncService
{
    protected $omieService;

    public function __construct(OmieService $omieService)
    {
        $this->omieService = $omieService;
    }

    /**
     * Sincroniza centros de custo da API Omie
     */
    public function syncCentrosCusto(int $pagina = 1, int $registrosPorPagina = 50): array
    {
        try {
            Log::info('Iniciando sincronização de centros de custo', ['pagina' => $pagina]);

            Log::info('Chamando omieService->listDepartments', ['pagina' => $pagina, 'registrosPorPagina' => $registrosPorPagina]);
            $response = $this->omieService->listDepartments($pagina, $registrosPorPagina);
            Log::info('Resposta recebida da API', [
                'response_keys' => array_keys($response ?? []), 
                'success' => $response['success'] ?? false,
                'pagination' => $response['pagination'] ?? null
            ]);
            
            if (!$response || !$response['success']) {
                throw new \Exception('Resposta inválida da API Omie para centros de custo: ' . ($response['message'] ?? 'Erro desconhecido'));
            }
            
            $centrosCustoData = $response['data'] ?? [];
            Log::info('Dados dos centros de custo extraídos', ['total_centros_custo' => count($centrosCustoData)]);

            $centrosCustoProcessados = 0;
            $centrosCustoCriados = 0;
            $centrosCustoAtualizados = 0;
            $erros = [];

            foreach ($centrosCustoData as $centroCustoData) {
                try {
                    $resultado = $this->processarCentroCusto($centroCustoData);
                    
                    if ($resultado['criado']) {
                        $centrosCustoCriados++;
                    } else {
                        $centrosCustoAtualizados++;
                    }
                    
                    $centrosCustoProcessados++;
                } catch (\Exception $e) {
                    $erros[] = [
                        'codigo_omie' => $centroCustoData['codigo_departamento_omie'] ?? 'N/A',
                        'erro' => $e->getMessage()
                    ];
                    Log::error('Erro ao processar centro de custo', [
                        'codigo_omie' => $centroCustoData['codigo_departamento_omie'] ?? 'N/A',
                        'erro' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Sincronização de centros de custo concluída', [
                'processados' => $centrosCustoProcessados,
                'criados' => $centrosCustoCriados,
                'atualizados' => $centrosCustoAtualizados,
                'erros' => count($erros)
            ]);

            return [
                'sucesso' => true,
                'processados' => $centrosCustoProcessados,
                'criados' => $centrosCustoCriados,
                'atualizados' => $centrosCustoAtualizados,
                'erros' => $erros,
                'total_paginas' => $response['pagination']['total_pages'] ?? 1,
                'pagina_atual' => $response['pagination']['current_page'] ?? 1,
                'total_registros' => $response['pagination']['total'] ?? 0
            ];

        } catch (\Exception $e) {
            Log::error('Erro na sincronização de centros de custo', ['erro' => $e->getMessage()]);
            
            return [
                'sucesso' => false,
                'erro' => $e->getMessage(),
                'processados' => 0,
                'criados' => 0,
                'atualizados' => 0,
                'erros' => []
            ];
        }
    }

    /**
     * Sincroniza todos os centros de custo
     */
    public function syncTodos(): array
    {
        $resultadoGeral = [
            'centros_custo' => ['processados' => 0, 'criados' => 0, 'atualizados' => 0, 'erros' => []],
            'sucesso' => true,
            'mensagem' => ''
        ];

        try {
            // Sincronizar centros de custo
            $pagina = 1;
            do {
                $resultado = $this->syncCentrosCusto($pagina);
                
                if (!$resultado['sucesso']) {
                    $resultadoGeral['sucesso'] = false;
                    $resultadoGeral['mensagem'] .= "Erro na sincronização de centros de custo: " . $resultado['erro'] . "; ";
                    break;
                }

                $resultadoGeral['centros_custo']['processados'] += $resultado['processados'];
                $resultadoGeral['centros_custo']['criados'] += $resultado['criados'];
                $resultadoGeral['centros_custo']['atualizados'] += $resultado['atualizados'];
                $resultadoGeral['centros_custo']['erros'] = array_merge($resultadoGeral['centros_custo']['erros'], $resultado['erros']);

                $pagina++;
            } while ($pagina <= ($resultado['total_paginas'] ?? 1));

            if ($resultadoGeral['sucesso']) {
                $resultadoGeral['mensagem'] = 'Sincronização de centros de custo concluída com sucesso';
            }

        } catch (\Exception $e) {
            $resultadoGeral['sucesso'] = false;
            $resultadoGeral['mensagem'] = 'Erro geral na sincronização de centros de custo: ' . $e->getMessage();
            Log::error('Erro geral na sincronização de centros de custo', ['erro' => $e->getMessage()]);
        }

        return $resultadoGeral;
    }

    /**
     * Processa um centro de custo individual
     */
    protected function processarCentroCusto(array $data): array
    {
        $codigoOmie = $data['codigo_departamento_omie'] ?? null;
        
        if (!$codigoOmie) {
            throw new \Exception('Código Omie não encontrado nos dados do centro de custo');
        }

        // Buscar registro existente
        $centroCusto = CentroCusto::where('codigo_departamento_omie', $codigoOmie)->first();

        $dadosParaSalvar = $this->mapearDadosApi($data);

        if ($centroCusto) {
            // Atualizar registro existente, preservando associações específicas do OBM
            $clienteIdAtual = $centroCusto->cliente_id;
            $baseIdAtual = $centroCusto->base_id;
            $supervisorAtual = $centroCusto->supervisor;
            
            $centroCusto->update($dadosParaSalvar);
            
            // Restaurar as associações específicas do OBM se elas existiam
            $dadosParaRestaurar = [];
            if ($clienteIdAtual) {
                $dadosParaRestaurar['cliente_id'] = $clienteIdAtual;
            }
            if ($baseIdAtual) {
                $dadosParaRestaurar['base_id'] = $baseIdAtual;
            }
            if ($supervisorAtual) {
                $dadosParaRestaurar['supervisor'] = $supervisorAtual;
            }
            
            if (!empty($dadosParaRestaurar)) {
                $centroCusto->update($dadosParaRestaurar);
            }
            
            $centroCusto->marcarComoSincronizado();
            
            return ['criado' => false, 'registro' => $centroCusto];
        } else {
            // Criar novo registro
            $centroCusto = CentroCusto::create($dadosParaSalvar);
            $centroCusto->marcarComoSincronizado();
            
            return ['criado' => true, 'registro' => $centroCusto];
        }
    }

    /**
     * Mapeia dados da API para o formato do banco
     */
    protected function mapearDadosApi(array $data): array
    {
        return [
            'codigo_departamento_omie' => $data['codigo_departamento_omie'] ?? null,
            'codigo_departamento_integracao' => $data['codigo_departamento_integracao'] ?? null,
            'nome' => $data['nome_departamento'] ?? null,
            'descricao' => $data['descricao_departamento'] ?? null,
            'inativo' => $data['inativo'] ?? 'N',
            'importado_api' => 'S',
            'dados_originais_api' => json_encode($data),
            'data_inclusao' => isset($data['data_inclusao']) ? Carbon::parse($data['data_inclusao']) : null,
            'data_alteracao' => isset($data['data_alteracao']) ? Carbon::parse($data['data_alteracao']) : null,
            // Nota: campos específicos do OBM não são mapeados aqui pois não vêm da API Omie:
            // - cliente_id: associação com cliente será feita manualmente no sistema OBM
            // - base_id: associação com base será feita manualmente no sistema OBM
            // - supervisor: supervisor será definido manualmente no sistema OBM
        ];
    }
}