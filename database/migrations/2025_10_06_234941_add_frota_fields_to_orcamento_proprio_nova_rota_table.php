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
            $table->integer('quantidade_dias')->default(1)->after('valor_aluguel_frota');
            $table->decimal('valor_combustivel', 10, 2)->default(0)->after('quantidade_dias');
            $table->boolean('incluir_pedagio')->default(false)->after('valor_combustivel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamento_proprio_nova_rota', function (Blueprint $table) {
            $table->dropColumn(['quantidade_dias', 'valor_combustivel', 'incluir_pedagio']);
        });
    }
};
