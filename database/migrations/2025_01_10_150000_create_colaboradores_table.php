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
        Schema::create('colaboradores', function (Blueprint $table) {
            $table->id();
            
            // Dados pessoais
            $table->string('nome', 100)->comment('Nome completo do colaborador');
            $table->string('cpf', 14)->unique()->comment('CPF do colaborador');
            $table->string('rg', 20)->nullable()->comment('RG do colaborador');
            $table->date('data_nascimento')->nullable()->comment('Data de nascimento');
            $table->string('telefone', 20)->nullable()->comment('Telefone de contato');
            $table->string('email', 100)->nullable()->comment('E-mail do colaborador');
            
            // Dados contratuais
            $table->date('data_admissao')->comment('Data de admissão');
            
            // Relacionamentos
            $table->foreignId('cargo_id')->constrained('recursos_humanos')->onDelete('restrict')->comment('Cargo do colaborador');
            $table->foreignId('base_id')->nullable()->constrained('bases')->onDelete('set null')->comment('Base operacional');
            
            // Status
            $table->boolean('status')->default(true)->comment('Status ativo/inativo');
            
            $table->timestamps();
            
            // Índices para performance
            $table->index('cpf');
            $table->index('nome');
            $table->index(['cargo_id', 'status']);
            $table->index(['base_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colaboradores');
    }
};