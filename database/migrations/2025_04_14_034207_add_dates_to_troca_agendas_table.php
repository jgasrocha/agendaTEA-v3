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
        Schema::table('troca_agendas', function (Blueprint $table) {
            $table->enum('status', ['pendente', 'aceita', 'rejeitada', 'expirada'])
            ->default('pendente')
            ->change();

            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('troca_agendas', function (Blueprint $table) {
            $table->enum('status', ['pendente', 'aceita', 'rejeitada', 'expirada'])
            ->default('pendente')
            ->change();

            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
        });
    }
};
