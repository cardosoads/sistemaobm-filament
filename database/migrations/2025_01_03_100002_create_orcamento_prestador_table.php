<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamento_prestador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos');
            $table->string('fornecedor_omie_id', 100)->nullable();
            $table->string('fornecedor_nome', 200)->nullable();
            $table->decimal('valor_referencia', 10, 2)->default(0);
            $table->integer('qtd_dias')->default(1);
            $table->decimal('custo_fornecedor', 10, 2)->default(0);
            $table->decimal('lucro_percentual', 5, 2)->default(0);
            $table->decimal('valor_lucro', 10, 2)->default(0);
            $table->decimal('impostos_percentual', 5, 2)->default(0);
            $table->decimal('valor_impostos', 10, 2)->default(0);
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->foreignId('grupo_imposto_id')->nullable()->constrained('grupos_impostos');
            $table->timestamps();
            
            $table->index('orcamento_id');
            $table->index('fornecedor_nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamento_prestador');
    }
};