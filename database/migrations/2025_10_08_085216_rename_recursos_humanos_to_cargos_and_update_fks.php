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
        // Renomeia a tabela se ainda existir com o nome antigo
        if (Schema::hasTable('recursos_humanos') && !Schema::hasTable('cargos')) {
            Schema::rename('recursos_humanos', 'cargos');
        }

        // Atualiza FK de colaboradores.cargo_id para apontar para cargos
        Schema::table('colaboradores', function (Blueprint $table) {
            // Drop da FK antiga se existir
            try { $table->dropForeign(['cargo_id']); } catch (\Throwable $e) {}
            // Cria a nova FK
            $table->foreign('cargo_id')->references('id')->on('cargos')->onDelete('restrict');
        });

        // Atualiza FK de obms.colaborador_id para apontar para colaboradores
        Schema::table('obms', function (Blueprint $table) {
            try { $table->dropForeign(['colaborador_id']); } catch (\Throwable $e) {}
            $table->foreign('colaborador_id')->references('id')->on('colaboradores')->onDelete('set null');
        });

        // Atualiza FKs que referenciam recurso_humano para apontar para cargos
        Schema::table('orcamentos', function (Blueprint $table) {
            try { $table->dropForeign(['recurso_humano_id']); } catch (\Throwable $e) {}
            $table->foreign('recurso_humano_id')->references('id')->on('cargos')->onDelete('set null');
        });

        Schema::table('orcamento_proprio_nova_rota', function (Blueprint $table) {
            try { $table->dropForeign(['recurso_humano_id']); } catch (\Throwable $e) {}
            $table->foreign('recurso_humano_id')->references('id')->on('cargos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverte FKs
        Schema::table('orcamento_proprio_nova_rota', function (Blueprint $table) {
            try { $table->dropForeign(['recurso_humano_id']); } catch (\Throwable $e) {}
            $table->foreign('recurso_humano_id')->references('id')->on('recursos_humanos')->onDelete('set null');
        });

        Schema::table('orcamentos', function (Blueprint $table) {
            try { $table->dropForeign(['recurso_humano_id']); } catch (\Throwable $e) {}
            $table->foreign('recurso_humano_id')->references('id')->on('recursos_humanos')->onDelete('set null');
        });

        Schema::table('obms', function (Blueprint $table) {
            try { $table->dropForeign(['colaborador_id']); } catch (\Throwable $e) {}
            $table->foreign('colaborador_id')->references('id')->on('recursos_humanos')->onDelete('set null');
        });

        Schema::table('colaboradores', function (Blueprint $table) {
            try { $table->dropForeign(['cargo_id']); } catch (\Throwable $e) {}
            $table->foreign('cargo_id')->references('id')->on('recursos_humanos')->onDelete('restrict');
        });

        // Renomeia a tabela de volta, se aplicável
        if (Schema::hasTable('cargos') && !Schema::hasTable('recursos_humanos')) {
            Schema::rename('cargos', 'recursos_humanos');
        }
    }
};
