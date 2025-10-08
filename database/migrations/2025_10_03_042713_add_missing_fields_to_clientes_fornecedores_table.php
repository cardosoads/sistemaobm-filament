<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clientes_fornecedores', function (Blueprint $table) {
            // Campo principal solicitado pelo usuário
            $table->boolean('is_cliente')->default(true)->after('id'); // true = cliente, false = fornecedor
            
            // Campos de integração com Omie
            $table->string('codigo_cliente_integracao')->nullable()->after('codigo_cliente_omie');
            
            // Campos de contato adicionais
            $table->string('fax_ddd', 3)->nullable()->after('telefone2_numero');
            $table->string('fax_numero', 15)->nullable()->after('fax_ddd');
            
            // Campos fiscais adicionais
            $table->string('contribuinte', 1)->default('N')->after('optante_simples_nacional'); // N = não, S = sim
            $table->string('exterior', 1)->default('N')->after('contribuinte'); // N = nacional, S = exterior
            $table->string('importado_api', 1)->default('N')->after('exterior'); // N = manual, S = importado da API
            
            // Campos de observações e informações adicionais
            $table->text('observacoes')->nullable()->after('avatar');
            $table->text('obs_detalhadas')->nullable()->after('observacoes');
            $table->text('recomendacao_atraso')->nullable()->after('obs_detalhadas');
            
            // Dados originais da API para referência
            $table->json('dados_originais_api')->nullable()->after('recomendacao_atraso');
            
            // Campos de controle de sincronização
            $table->timestamp('ultima_sincronizacao')->nullable()->after('dados_originais_api');
            $table->string('status_sincronizacao')->default('pendente')->after('ultima_sincronizacao'); // pendente, sincronizado, erro
            
            // Índices adicionais
            $table->index(['is_cliente']);
            $table->index(['codigo_cliente_integracao']);
            $table->index(['status_sincronizacao']);
            $table->index(['ultima_sincronizacao']);
            $table->index(['importado_api']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes_fornecedores', function (Blueprint $table) {
            $table->dropIndex(['is_cliente']);
            $table->dropIndex(['codigo_cliente_integracao']);
            $table->dropIndex(['status_sincronizacao']);
            $table->dropIndex(['ultima_sincronizacao']);
            $table->dropIndex(['importado_api']);
            
            $table->dropColumn([
                'is_cliente',
                'codigo_cliente_integracao',
                'fax_ddd',
                'fax_numero',
                'contribuinte',
                'exterior',
                'importado_api',
                'observacoes',
                'obs_detalhadas',
                'recomendacao_atraso',
                'dados_originais_api',
                'ultima_sincronizacao',
                'status_sincronizacao'
            ]);
        });
    }
};
