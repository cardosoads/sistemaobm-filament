<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamento_proprio_nova_rota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos');
            
            // Informações da rota
            $table->string('origem', 200)->nullable();
            $table->string('destino', 200)->nullable();
            $table->decimal('km_rodado', 10, 2)->default(0);
            $table->decimal('valor_km_rodado', 10, 2)->default(0);
            
            // Informações do funcionário
            $table->foreignId('recurso_humano_id')->nullable()->constrained('recursos_humanos');
            $table->foreignId('base_id')->nullable()->constrained('bases');
            $table->decimal('valor_funcionario', 10, 2)->default(0);
            
            // Informações do veículo próprio
            $table->foreignId('frota_id')->nullable()->constrained('frotas');
            $table->decimal('valor_aluguel_frota', 10, 2)->default(0);
            
            // Informações do fornecedor
            $table->string('fornecedor_omie_id', 100)->nullable();
            $table->string('fornecedor_nome', 200)->nullable();
            $table->string('fornecedor_referencia', 200)->nullable();
            $table->integer('fornecedor_dias')->default(1);
            $table->decimal('fornecedor_custo', 10, 2)->default(0);
            $table->decimal('fornecedor_lucro', 10, 2)->default(0);
            $table->decimal('fornecedor_impostos', 10, 2)->default(0);
            $table->decimal('fornecedor_total', 10, 2)->default(0);
            
            // Totais
            $table->decimal('valor_total_rotas', 10, 2)->default(0);
            $table->decimal('valor_total_geral', 10, 2)->default(0);
            $table->decimal('lucro_percentual', 5, 2)->default(0);
            $table->decimal('valor_lucro', 10, 2)->default(0);
            $table->decimal('impostos_percentual', 5, 2)->default(0);
            $table->decimal('valor_impostos', 10, 2)->default(0);
            $table->decimal('valor_final', 10, 2)->default(0);
            
            $table->foreignId('grupo_imposto_id')->nullable()->constrained('grupos_impostos');
            $table->timestamps();
            
            $table->index('orcamento_id');
            $table->index('fornecedor_nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamento_proprio_nova_rota');
    }
};