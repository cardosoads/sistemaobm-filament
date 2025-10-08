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
        Schema::table('centros_custo', function (Blueprint $table) {
            // Adiciona campo para associação com cliente
            // Este campo será preenchido após sincronização e é específico do sistema OBM
            $table->unsignedBigInteger('cliente_id')->nullable()->after('descricao');
            
            // Adiciona índice para performance
            $table->index('cliente_id');
            
            // Adiciona foreign key constraint
            $table->foreign('cliente_id')->references('id')->on('clientes_fornecedores')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('centros_custo', function (Blueprint $table) {
            // Remove foreign key constraint
            $table->dropForeign(['cliente_id']);
            
            // Remove índice
            $table->dropIndex(['cliente_id']);
            
            // Remove campo
            $table->dropColumn('cliente_id');
        });
    }
};
