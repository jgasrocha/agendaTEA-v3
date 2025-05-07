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
        Schema::create('troca_agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('agenda_original_id')->constrained('agenda_fixas')->onDelete('cascade');
            $table->foreignId('agenda_desejada_id')->constrained('agenda_fixas')->onDelete('cascade');
            $table->enum('status', ['pendente', 'aceita', 'rejeitada'])->default('pendente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('troca_agendas');
    }
};
