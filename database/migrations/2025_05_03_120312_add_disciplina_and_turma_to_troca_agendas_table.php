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
            $table->foreignId('disciplina_original_id')->nullable()->constrained('disciplinas')->onDelete('cascade');
            $table->foreignId('disciplina_desejada_id')->nullable()->constrained('disciplinas')->onDelete('cascade');
            $table->foreignId('turma_id')->nullable()->nullable()->constrained('turmas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('troca_agendas', function (Blueprint $table) {
            $table->dropForeign(['disciplina_original_id']);
            $table->dropForeign(['disciplina_desejada_id']);
            $table->dropForeign(['turma_id']);

            $table->dropColumn('disciplina_original_id');
            $table->dropColumn('disciplina_desejada_id');
            $table->dropColumn('turma_id');
        });
    }
};
