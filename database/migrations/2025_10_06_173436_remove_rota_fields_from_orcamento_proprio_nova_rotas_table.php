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
        Schema::table('orcamento_proprio_nova_rota', function (Blueprint $table) {
            $table->dropColumn(['origem', 'destino', 'km_rodado', 'valor_km_rodado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orcamento_proprio_nova_rota', function (Blueprint $table) {
            $table->string('origem', 200)->nullable();
            $table->string('destino', 200)->nullable();
            $table->decimal('km_rodado', 10, 2)->default(0);
            $table->decimal('valor_km_rodado', 10, 2)->default(0);
        });
    }
};
