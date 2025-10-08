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
        Schema::table('orcamento_aumento_km', function (Blueprint $table) {
            // Remover campos antigos
            $table->dropColumn([
                'km_rodado',
                'km_extra', 
                'valor_km_extra',
                'litros_combustivel',
                'horas_extras',
                'valor_hora_extra',
                'valor_pedagio',
                'lucro_percentual',
                'impostos_percentual'
            ]);
            
            // Adicionar novos campos
            $table->decimal('km_por_dia', 10, 2)->default(0)->after('orcamento_id');
            $table->integer('quantidade_dias_aumento')->default(0)->after('km_por_dia');
            $table->decimal('combustivel_km_litro', 10, 2)->default(0)->after('quantidade_dias_aumento');
            $table->decimal('hora_extra', 10, 2)->default(0)->after('valor_combustivel');
            $table->decimal('pedagio', 10, 2)->default(0)->after('hora_extra');
            $table->decimal('percentual_lucro', 5, 2)->default(0)->after('pedagio');
            $table->decimal('percentual_impostos', 5, 2)->default(0)->after('grupo_imposto_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamento_aumento_km', function (Blueprint $table) {
            // Remover novos campos
            $table->dropColumn([
                'km_por_dia',
                'quantidade_dias_aumento',
                'combustivel_km_litro',
                'hora_extra',
                'pedagio',
                'percentual_lucro',
                'percentual_impostos'
            ]);
            
            // Restaurar campos antigos
            $table->decimal('km_rodado', 10, 2)->default(0)->after('orcamento_id');
            $table->decimal('km_extra', 10, 2)->default(0)->after('km_rodado');
            $table->decimal('valor_km_extra', 10, 2)->default(0)->after('km_extra');
            $table->decimal('litros_combustivel', 10, 2)->default(0)->after('valor_km_extra');
            $table->integer('horas_extras')->default(0)->after('valor_combustivel');
            $table->decimal('valor_hora_extra', 10, 2)->default(0)->after('horas_extras');
            $table->decimal('valor_pedagio', 10, 2)->default(0)->after('valor_hora_extra');
            $table->decimal('lucro_percentual', 5, 2)->default(0)->after('valor_total');
            $table->decimal('impostos_percentual', 5, 2)->default(0)->after('valor_lucro');
        });
    }
};
