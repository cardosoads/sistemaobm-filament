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
            // Adiciona campo para ID do fornecedor Omie
            $table->string('fornecedor_omie_id', 100)->nullable()->after('cliente_omie_id');
            
            // Adiciona índice para performance
            $table->index('fornecedor_omie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            // Remove índice
            $table->dropIndex(['fornecedor_omie_id']);
            
            // Remove campo
            $table->dropColumn('fornecedor_omie_id');
        });
    }
};
