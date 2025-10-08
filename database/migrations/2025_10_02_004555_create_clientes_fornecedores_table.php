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
        Schema::create('clientes_fornecedores', function (Blueprint $table) {
            $table->id();
            
            // Campos principais do cliente/fornecedor
            $table->bigInteger('codigo_cliente_omie')->unique()->nullable();
            $table->string('razao_social')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj_cpf', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('homepage')->nullable();
            
            // Telefones
            $table->string('telefone1_ddd', 3)->nullable();
            $table->string('telefone1_numero', 15)->nullable();
            $table->string('telefone2_ddd', 3)->nullable();
            $table->string('telefone2_numero', 15)->nullable();
            
            // Endereço
            $table->string('endereco')->nullable();
            $table->string('endereco_numero', 10)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep', 10)->nullable();
            
            // Status e configurações
            $table->char('inativo', 1)->default('N'); // N = ativo, S = inativo
            $table->json('tags')->nullable(); // Para armazenar tags como cliente/fornecedor
            
            // Campos adicionais
            $table->string('inscricao_estadual')->nullable();
            $table->string('inscricao_municipal')->nullable();
            $table->string('pessoa_fisica', 1)->default('N'); // N = jurídica, S = física
            $table->string('optante_simples_nacional', 1)->default('N');
            
            // Avatar/foto (para uso local)
            $table->string('avatar')->nullable();
            
            // Campos de controle
            $table->timestamp('data_inclusao')->nullable();
            $table->timestamp('data_alteracao')->nullable();
            $table->timestamps();
            
            // Índices para performance
            $table->index(['razao_social']);
            $table->index(['nome_fantasia']);
            $table->index(['cnpj_cpf']);
            $table->index(['email']);
            $table->index(['inativo']);
            $table->index(['cidade', 'estado']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes_fornecedores');
    }
};
