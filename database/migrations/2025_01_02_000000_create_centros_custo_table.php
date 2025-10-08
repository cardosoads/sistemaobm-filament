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
        Schema::create('centros_custo', function (Blueprint $table) {
            $table->id();
            
            // Campos de integração com Omie
            $table->bigInteger('codigo_departamento_omie')->nullable()->unique();
            $table->string('codigo_departamento_integracao')->nullable();
            
            // Dados básicos do departamento
            $table->string('nome', 255);
            $table->text('descricao')->nullable();
            
            // Status
            $table->string('inativo', 1)->default('N'); // N = ativo, S = inativo
            
            // Campos de controle de sincronização
            $table->string('importado_api', 1)->default('N'); // N = manual, S = importado da API
            $table->json('dados_originais_api')->nullable(); // Dados originais da API para referência
            $table->timestamp('ultima_sincronizacao')->nullable();
            $table->string('status_sincronizacao')->default('pendente'); // pendente, sincronizado, erro
            
            // Campos de auditoria
            $table->timestamp('data_inclusao')->nullable();
            $table->timestamp('data_alteracao')->nullable();
            $table->timestamps();
            
            // Índices para performance
            $table->index(['nome']);
            $table->index(['inativo']);
            $table->index(['status_sincronizacao']);
            $table->index(['ultima_sincronizacao']);
            $table->index(['importado_api']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centros_custo');
    }
};
