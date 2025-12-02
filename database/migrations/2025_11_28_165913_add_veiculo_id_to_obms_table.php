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
        Schema::table('obms', function (Blueprint $table) {
            $table->foreignId('veiculo_id')
                  ->nullable()
                  ->after('frota_id')
                  ->constrained('veiculos')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('obms', function (Blueprint $table) {
            $table->dropForeign(['veiculo_id']);
            $table->dropColumn('veiculo_id');
        });
    }
};
