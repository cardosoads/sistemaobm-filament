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
        Schema::table('centros_custo', function (Blueprint $table) {
            $table->unsignedBigInteger('base_id')->nullable()->after('cliente_id');
            $table->string('supervisor')->nullable()->after('base_id');
            
            $table->foreign('base_id')->references('id')->on('bases')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('centros_custo', function (Blueprint $table) {
            $table->dropForeign(['base_id']);
            $table->dropColumn(['base_id', 'supervisor']);
        });
    }
};
