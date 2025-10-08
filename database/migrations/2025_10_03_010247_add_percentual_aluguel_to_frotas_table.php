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
        Schema::table('frotas', function (Blueprint $table) {
            $table->decimal('percentual_aluguel', 5, 2)->default(0)->after('percentual_fipe')->comment('Percentual de aluguel sobre o valor FIPE');
            $table->index('percentual_aluguel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frotas', function (Blueprint $table) {
            $table->dropIndex(['percentual_aluguel']);
            $table->dropColumn('percentual_aluguel');
        });
    }
};
