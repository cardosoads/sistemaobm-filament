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
            // Adiciona as colunas de grupo_imposto para os diferentes tipos de orçamento
            $table->foreignId('grupo_imposto_id')->nullable()->constrained('grupos_impostos')->after('user_id');
            $table->foreignId('grupo_imposto_id_aumento')->nullable()->constrained('grupos_impostos')->after('grupo_imposto_id');
            $table->foreignId('grupo_imposto_id_rota')->nullable()->constrained('grupos_impostos')->after('grupo_imposto_id_aumento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamentos', function (Blueprint $table) {
            // Remove as colunas de grupo_imposto
            $table->dropForeign(['grupo_imposto_id']);
            $table->dropForeign(['grupo_imposto_id_aumento']);
            $table->dropForeign(['grupo_imposto_id_rota']);
            $table->dropColumn(['grupo_imposto_id', 'grupo_imposto_id_aumento', 'grupo_imposto_id_rota']);
        });
    }
};
