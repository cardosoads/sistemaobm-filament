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
        Schema::table('orcamento_proprio_nova_rota', function (Blueprint $table) {
            $table->boolean('incluir_funcionario')->default(false)->after('orcamento_id');
            $table->boolean('incluir_frota')->default(false)->after('incluir_funcionario');
            $table->boolean('incluir_fornecedor')->default(false)->after('incluir_frota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamento_proprio_nova_rota', function (Blueprint $table) {
            $table->dropColumn(['incluir_funcionario', 'incluir_frota', 'incluir_fornecedor']);
        });
    }
};
