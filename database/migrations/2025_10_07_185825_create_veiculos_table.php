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
        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->string('placa', 8)->unique();
            $table->string('renavam', 20)->unique();
            $table->string('chassi', 30)->unique();
            $table->string('ano_modelo', 9);
            $table->string('cor', 50);
            $table->string('marca_modelo', 100);
            $table->string('tipo_combustivel', 30);
            $table->enum('status', ['ativo', 'inativo', 'manutencao', 'vendido'])->default('ativo');
            $table->foreignId('tipo_veiculo_id')->constrained('tipos_veiculos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veiculos');
    }
};
