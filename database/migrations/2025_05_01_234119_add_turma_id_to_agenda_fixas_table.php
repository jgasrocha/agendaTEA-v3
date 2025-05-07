<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agenda_fixas', function (Blueprint $table) {
            $table->foreignId('turma_id')->nullable()->constrained()->onDelete('cascade');
        });

        DB::table('agenda_fixas')->update(['turma_id' => 1]);

    // Altera para not nullable após popular os dados
        Schema::table('agenda_fixas', function (Blueprint $table) {
            $table->foreignId('turma_id')
                  ->nullable(false)
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda_fixas', function (Blueprint $table) {
            $table->dropForeign(['turma_id']);

            $table->dropColumn('turma_id');
        });
    }
};
