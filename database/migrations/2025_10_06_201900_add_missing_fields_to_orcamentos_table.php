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
        Schema::table('orcamentos', function (Blueprint $table) {
            // Campos de controle para tipo próprio nova rota
            $table->boolean('incluir_funcionario')->default(false)->after('data_orcamento');
            $table->boolean('incluir_frota')->default(false)->after('incluir_funcionario');
            $table->boolean('incluir_prestador')->default(false)->after('incluir_frota');
            
            // Campos de funcionário
            $table->foreignId('base_id')->nullable()->constrained('bases')->after('incluir_prestador');
            $table->foreignId('recurso_humano_id')->nullable()->constrained('recursos_humanos')->after('base_id');
            $table->decimal('valor_funcionario', 10, 2)->default(0)->after('recurso_humano_id');
            
            // Campos de frota
            $table->foreignId('frota_id')->nullable()->constrained('frotas')->after('valor_funcionario');
            $table->decimal('valor_aluguel_frota', 10, 2)->default(0)->after('frota_id');
            
            // Campos de fornecedor para rota
            $table->string('fornecedor_omie_id_rota', 100)->nullable()->after('valor_aluguel_frota');
            $table->decimal('fornecedor_referencia', 10, 2)->default(0)->after('fornecedor_omie_id_rota');
            $table->integer('fornecedor_dias')->default(1)->after('fornecedor_referencia');
            
            // Campos de percentuais para rota
            $table->decimal('lucro_percentual_rota', 5, 2)->default(0)->after('fornecedor_dias');
            $table->decimal('impostos_percentual_rota', 5, 2)->default(0)->after('lucro_percentual_rota');
            
            // Adicionar índices para performance
            $table->index('incluir_funcionario');
            $table->index('incluir_frota');
            $table->index('incluir_prestador');
            $table->index('fornecedor_omie_id_rota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            // Remover índices
            $table->dropIndex(['incluir_funcionario']);
            $table->dropIndex(['incluir_frota']);
            $table->dropIndex(['incluir_prestador']);
            $table->dropIndex(['fornecedor_omie_id_rota']);
            
            // Remover foreign keys
            $table->dropForeign(['base_id']);
            $table->dropForeign(['recurso_humano_id']);
            $table->dropForeign(['frota_id']);
            
            // Remover colunas
            $table->dropColumn([
                'incluir_funcionario',
                'incluir_frota',
                'incluir_prestador',
                'base_id',
                'recurso_humano_id',
                'valor_funcionario',
                'frota_id',
                'valor_aluguel_frota',
                'fornecedor_omie_id_rota',
                'fornecedor_referencia',
                'fornecedor_dias',
                'lucro_percentual_rota',
                'impostos_percentual_rota'
            ]);
        });
    }
};