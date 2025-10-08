<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamento_aumento_km', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos');
            $table->decimal('km_rodado', 10, 2)->default(0);
            $table->decimal('km_extra', 10, 2)->default(0);
            $table->decimal('valor_km_extra', 10, 2)->default(0);
            $table->decimal('litros_combustivel', 10, 2)->default(0);
            $table->decimal('valor_combustivel', 10, 2)->default(0);
            $table->integer('horas_extras')->default(0);
            $table->decimal('valor_hora_extra', 10, 2)->default(0);
            $table->decimal('valor_pedagio', 10, 2)->default(0);
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->decimal('lucro_percentual', 5, 2)->default(0);
            $table->decimal('valor_lucro', 10, 2)->default(0);
            $table->decimal('impostos_percentual', 5, 2)->default(0);
            $table->decimal('valor_impostos', 10, 2)->default(0);
            $table->decimal('valor_final', 10, 2)->default(0);
            $table->foreignId('grupo_imposto_id')->nullable()->constrained('grupos_impostos');
            $table->timestamps();
            
            $table->index('orcamento_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamento_aumento_km');
    }
};