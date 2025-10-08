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
        Schema::create('bases', function (Blueprint $table) {
            $table->id();
            $table->string('uf', 2); // UF - Estado (2 caracteres)
            $table->string('base'); // BASE - Cidade
            $table->string('regional'); // Regional - Região do país
            $table->string('sigla', 10); // Sigla - Campo digitável
            $table->boolean('status')->default(true); // Status - Ativa/Inativa
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bases');
    }
};
