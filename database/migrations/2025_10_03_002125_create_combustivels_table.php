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
        Schema::create('combustivels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('base_id')->constrained('bases')->onDelete('restrict')->comment('Base de abastecimento');
            $table->string('convenio')->comment('Convênio de abastecimento');
            $table->decimal('preco_litro', 8, 3)->comment('Preço por litro');
            $table->boolean('active')->default(true)->comment('Status ativo/inativo');
            $table->timestamps();
            
            // Índices
            $table->index('base_id');
            $table->index('active');
            $table->index('preco_litro');
            
            // Índice único para evitar duplicação de convênio por base
            $table->unique(['base_id', 'convenio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combustivels');
    }
};
