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
        Schema::create('frotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_veiculo_id')->constrained('tipos_veiculos')->onDelete('restrict')->comment('Tipo de veículo');
            $table->decimal('fipe', 10, 2)->comment('Valor FIPE do veículo');
            $table->decimal('percentual_fipe', 5, 2)->default(0)->comment('Percentual sobre o valor FIPE');
            $table->decimal('aluguel_carro', 10, 2)->default(0)->comment('Valor do aluguel do carro');
            $table->decimal('rastreador', 10, 2)->default(0)->comment('Valor do rastreador');
            $table->decimal('percentual_provisoes_avarias', 5, 2)->default(0)->comment('Percentual de provisões para avarias');
            $table->decimal('provisoes_avarias', 10, 2)->default(0)->comment('Valor das provisões para avarias');
            $table->decimal('percentual_provisao_desmobilizacao', 5, 2)->default(0)->comment('Percentual de provisão para desmobilização');
            $table->decimal('provisao_desmobilizacao', 10, 2)->default(0)->comment('Valor da provisão para desmobilização');
            $table->decimal('percentual_provisao_rac', 5, 2)->default(0)->comment('Percentual de provisão RAC');
            $table->decimal('provisao_diaria_rac', 10, 2)->default(0)->comment('Valor da provisão diária RAC');
            $table->decimal('custo_total', 10, 2)->default(0)->comment('Custo total calculado');
            $table->boolean('active')->default(true)->comment('Status ativo/inativo');
            $table->timestamps();
            
            // Índices
            $table->index('tipo_veiculo_id');
            $table->index('active');
            $table->index('custo_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frotas');
    }
};
