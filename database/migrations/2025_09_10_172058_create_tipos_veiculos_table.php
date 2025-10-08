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
        Schema::create('tipos_veiculos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique()->comment('Código do tipo de veículo (ex: Passeio, P-Cargo)');
            $table->decimal('consumo_km_litro', 8, 2)->comment('Consumo em KM por litro');
            $table->enum('tipo_combustivel', ['Gasolina', 'Etanol', 'Diesel', 'Flex'])->comment('Tipo de combustível');
            $table->string('descricao')->comment('Descrição detalhada do tipo de veículo');
            $table->boolean('active')->default(true)->comment('Status ativo/inativo');
            $table->timestamps();
            
            // Índices
            $table->index('codigo');
            $table->index('tipo_combustivel');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_veiculos');
    }
};
