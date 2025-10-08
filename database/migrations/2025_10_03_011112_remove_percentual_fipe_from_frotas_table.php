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
            $table->dropColumn('percentual_fipe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frotas', function (Blueprint $table) {
            $table->decimal('percentual_fipe', 5, 2)->default(0)->after('fipe')->comment('Percentual sobre o valor FIPE');
        });
    }
};
