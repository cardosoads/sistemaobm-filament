<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecursosHumanosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recursos_humanos', function (Blueprint $table) {
            $table->id();
            
            // Campos básicos de identificação
            $table->string('tipo_contratacao', 100)->comment('Tipo de contratação (CLT, PJ, etc.)');
            $table->string('cargo', 100)->comment('Cargo (Motorista, Motorista Líder, etc.)');
            
            // Relacionamento com base
            $table->foreignId('base_id')->nullable()->constrained('bases')->onDelete('set null')->comment('Base operacional');
            
            // Campos salariais básicos
            $table->decimal('base_salarial', 10, 2)->default(0)->comment('Base salarial de referência');
            $table->decimal('salario_base', 10, 2)->default(0)->comment('Salário base do funcionário');
            
            // Adicionais salariais
            $table->decimal('insalubridade', 10, 2)->default(0)->comment('Valor de insalubridade');
            $table->decimal('periculosidade', 10, 2)->default(0)->comment('Valor de periculosidade');
            $table->decimal('horas_extras', 10, 2)->default(0)->comment('Valor de horas extras');
            $table->decimal('adicional_noturno', 10, 2)->default(0)->comment('Adicional noturno');
            $table->decimal('extras', 10, 2)->default(0)->comment('Outros valores extras');
            
            // Benefícios
            $table->decimal('vale_transporte', 10, 2)->default(0)->comment('Vale transporte');
            $table->decimal('beneficios', 10, 2)->default(0)->comment('Total de benefícios');
            
            // Encargos e custos calculados
            $table->decimal('encargos_sociais', 10, 2)->default(0)->comment('Encargos sociais');
            $table->decimal('custo_total_mao_obra', 10, 2)->default(0)->comment('Custo total da mão de obra');
            
            // Percentuais para cálculos automáticos
            $table->decimal('percentual_encargos', 5, 2)->default(0)->comment('Percentual de encargos sociais');
            $table->decimal('percentual_beneficios', 5, 2)->default(0)->comment('Percentual de benefícios');
            
            // Controle de status
            $table->boolean('active')->default(true)->comment('Status ativo/inativo');
            
            $table->timestamps();
            
            // Índices para performance
            $table->index(['cargo', 'active']);
            $table->index(['base_id', 'active']);
            $table->index(['tipo_contratacao', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recursos_humanos');
    }
}
