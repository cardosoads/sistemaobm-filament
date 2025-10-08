<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CorreiosService
{
    private const BASE_URL = 'https://servicodados.ibge.gov.br/api/v1/localidades';
    
    /**
     * Busca todos os estados (UFs) do Brasil
     */
    public static function getUfs(): array
    {
        return Cache::remember('correios_ufs', 3600, function () {
            try {
                $response = Http::get(self::BASE_URL . '/estados');
                
                if ($response->successful()) {
                    $estados = $response->json();
                    
                    $ufs = [];
                    foreach ($estados as $estado) {
                        $ufs[$estado['sigla']] = $estado['sigla'] . ' - ' . $estado['nome'];
                    }
                    
                    // Ordena por sigla
                    ksort($ufs);
                    
                    return $ufs;
                }
            } catch (\Exception $e) {
                // Em caso de erro, retorna UFs estáticas
                return self::getStaticUfs();
            }
            
            return self::getStaticUfs();
        });
    }
    
    /**
     * Busca cidades por UF
     */
    public static function getCidadesByUf(string $uf): array
    {
        return Cache::remember("correios_cidades_{$uf}", 3600, function () use ($uf) {
            try {
                $response = Http::get(self::BASE_URL . "/estados/{$uf}/municipios");
                
                if ($response->successful()) {
                    $municipios = $response->json();
                    
                    $cidades = [];
                    foreach ($municipios as $municipio) {
                        $cidades[$municipio['nome']] = $municipio['nome'];
                    }
                    
                    // Ordena alfabeticamente
                    asort($cidades);
                    
                    return $cidades;
                }
            } catch (\Exception $e) {
                // Em caso de erro, retorna array vazio
                return [];
            }
            
            return [];
        });
    }
    
    /**
     * UFs estáticas como fallback
     */
    private static function getStaticUfs(): array
    {
        return [
            'AC' => 'AC - Acre',
            'AL' => 'AL - Alagoas',
            'AP' => 'AP - Amapá',
            'AM' => 'AM - Amazonas',
            'BA' => 'BA - Bahia',
            'CE' => 'CE - Ceará',
            'DF' => 'DF - Distrito Federal',
            'ES' => 'ES - Espírito Santo',
            'GO' => 'GO - Goiás',
            'MA' => 'MA - Maranhão',
            'MT' => 'MT - Mato Grosso',
            'MS' => 'MS - Mato Grosso do Sul',
            'MG' => 'MG - Minas Gerais',
            'PA' => 'PA - Pará',
            'PB' => 'PB - Paraíba',
            'PR' => 'PR - Paraná',
            'PE' => 'PE - Pernambuco',
            'PI' => 'PI - Piauí',
            'RJ' => 'RJ - Rio de Janeiro',
            'RN' => 'RN - Rio Grande do Norte',
            'RS' => 'RS - Rio Grande do Sul',
            'RO' => 'RO - Rondônia',
            'RR' => 'RR - Roraima',
            'SC' => 'SC - Santa Catarina',
            'SP' => 'SP - São Paulo',
            'SE' => 'SE - Sergipe',
            'TO' => 'TO - Tocantins',
        ];
    }
}