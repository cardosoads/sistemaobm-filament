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
        Schema::create('grupo_imposto_imposto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_imposto_id')->constrained('grupos_impostos')->onDelete('cascade');
            $table->foreignId('imposto_id')->constrained('impostos')->onDelete('cascade');
            $table->timestamps();
            
            // Evitar duplicatas
            $table->unique(['grupo_imposto_id', 'imposto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_imposto_imposto');
    }
};
