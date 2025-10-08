<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamentos', function (Blueprint $table) {
            $table->id();
            $table->date('data_solicitacao');
            $table->foreignId('centro_custo_id')->constrained('centros_custo');
            $table->string('numero_orcamento', 50)->unique();
            $table->string('nome_rota', 200)->nullable();
            $table->string('id_logcare', 100)->nullable();
            $table->string('cliente_omie_id', 100)->nullable();
            $table->string('cliente_nome', 200)->nullable();
            $table->string('horario', 50)->nullable();
            $table->string('frequencia_atendimento', 100)->nullable();
            $table->enum('tipo_orcamento', ['prestador', 'aumento_km', 'proprio_nova_rota']);
            $table->foreignId('user_id')->constrained('users');
            $table->date('data_orcamento')->nullable();
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->decimal('valor_impostos', 10, 2)->default(0);
            $table->decimal('valor_final', 10, 2)->default(0);
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado', 'cancelado'])->default('pendente');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            
            $table->index('numero_orcamento');
            $table->index('tipo_orcamento');
            $table->index('status');
            $table->index('data_solicitacao');
            $table->index('cliente_nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamentos');
    }
};