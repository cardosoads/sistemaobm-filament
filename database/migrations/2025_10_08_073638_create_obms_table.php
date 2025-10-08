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
        Schema::create('obms', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos principais
            $table->foreignId('orcamento_id')->constrained('orcamentos')->onDelete('restrict');
            $table->foreignId('colaborador_id')->nullable()->constrained('recursos_humanos')->onDelete('set null');
            $table->foreignId('frota_id')->nullable()->constrained('frotas')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            
            // Datas da execução
            $table->date('data_inicio');
            $table->date('data_fim');
            
            // Status
            $table->enum('status', ['pendente', 'em_andamento', 'concluida'])->default('pendente');
            
            // Observações
            $table->text('observacoes')->nullable();
            
            // Dados consolidados do orçamento (para referência rápida)
            $table->string('nome_rota', 255)->nullable();
            $table->string('cliente_nome', 255)->nullable();
            $table->string('origem', 200)->nullable();
            $table->string('destino', 200)->nullable();
            $table->decimal('valor_final', 10, 2)->nullable();
            
            $table->timestamps();
            
            // Índices para performance
            $table->index('status');
            $table->index('data_inicio');
            $table->index('data_fim');
            $table->index(['colaborador_id', 'data_inicio', 'data_fim']);
            $table->index(['frota_id', 'data_inicio', 'data_fim']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obms');
    }
};
