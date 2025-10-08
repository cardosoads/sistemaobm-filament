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
            $table->date('data_inicio')->nullable()->change();
            $table->date('data_fim')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('obms', function (Blueprint $table) {
            $table->date('data_inicio')->nullable(false)->change();
            $table->date('data_fim')->nullable(false)->change();
        });
    }
};