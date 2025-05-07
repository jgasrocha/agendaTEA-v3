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
        DB::statement('PRAGMA foreign_keys = OFF');

        // Remove a coluna se já existir
        if (Schema::hasColumn('disciplinas', 'curso_id')) {
            Schema::table('disciplinas', function (Blueprint $table) {
                $table->dropColumn('curso_id');
            });
        }
    
        // Adiciona a coluna corretamente
        Schema::table('disciplinas', function (Blueprint $table) {
            $table->foreignId('curso_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');
        });

        // 2. Criar curso padrão se necessário
        if (DB::table('cursos')->doesntExist()) {
            $cursoId = DB::table('cursos')->insertGetId([
                'nome' => 'Curso Padrão',
                'descricao' => 'Criado para migração',
                'imagem' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $cursoId = DB::table('cursos')->first()->id;
        }

        // 3. Atualizar disciplinas
        DB::table('disciplinas')->update(['curso_id' => $cursoId]);

        // 4. Alterar para NOT NULL
        Schema::table('disciplinas', function (Blueprint $table) {
            $table->foreignId('curso_id')
                ->nullable(false)
                ->change();
        });

        DB::statement('PRAGMA foreign_keys = ON');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disciplinas', function (Blueprint $table) {
            $table->dropForeign(['curso_id']);

            $table->dropColumn('curso_id');
        });
    }
};
