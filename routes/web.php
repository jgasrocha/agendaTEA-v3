<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DisciplinaController;
use App\Http\Controllers\AgendaFixaController;
use App\Http\Controllers\TrocaAgendaController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\TurmaController;
use App\Http\Middleware\CursoAdminMiddleware;
use App\Models\AgendaFixa;
use App\Models\Curso;
use App\Models\TrocaAgenda;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    $cursos = Curso::all();
    return Inertia::render('Cursos/Index', [
        'cursos' => $cursos,
        'auth' => ['user' => Auth::user()]
    ]);
})->name('home');

// Rotas públicas para cursos
Route::get('/cursos', [CursoController::class, 'index'])->name('cursos.index');
Route::get('/cursos/{curso}', [CursoController::class, 'show'])->name('cursos.show');

// Rotas públicas para turmas
Route::prefix('cursos/{curso}')->group(function () {
    Route::get('/turmas', [TurmaController::class, 'index'])->name('cursos.turmas.index');
    Route::get('/turmas/{turma}', [TurmaController::class, 'show'])->name('cursos.turmas.show');
    Route::get('/turmas/{turma}/agenda', [TurmaController::class, 'agenda'])->name('cursos.turmas.agenda.index');
});

// Rotas de autenticação
require __DIR__ . '/auth.php';

// Rotas protegidas - acesso comum (usuário autenticado)
Route::middleware(['auth', 'verified'])->group(function () {
    // Cursos (exceto show, que é público)
    Route::resource('cursos', CursoController::class)->except(['show']);

    Route::prefix('cursos/{curso}')->group(function () {
        // Turmas
        Route::get('turmas', [TurmaController::class, 'index'])->name('cursos.turmas.index');

        // Aqui estão as rotas para criar turmas (comuns e admin de curso)
        Route::get('turmas/create', [TurmaController::class, 'create'])
            ->name('cursos.turmas.create')
            ->middleware('can:create,App\Models\Turma,curso');

        Route::post('turmas', [TurmaController::class, 'store'])
            ->name('cursos.turmas.store')
            ->middleware('can:create,App\Models\Turma,curso');

        Route::get('turmas/{turma}/edit', [TurmaController::class, 'edit'])
            ->name('cursos.turmas.edit')
            ->middleware('can:update,turma');

        Route::put('turmas/{turma}', [TurmaController::class, 'update'])
            ->name('cursos.turmas.update')
            ->middleware('can:update,turma');

        Route::delete('turmas/{turma}', [TurmaController::class, 'destroy'])
            ->name('cursos.turmas.destroy')
            ->middleware('can:delete,turma');

        Route::get('turmas/{turma}', [TurmaController::class, 'show'])
            ->name('cursos.turmas.show');

        Route::get('turmas/{turma}/agenda', [TurmaController::class, 'agenda'])
            ->name('cursos.turmas.agenda.index');

        // Agendas Fixas (criação e exclusão)
        Route::prefix('turmas/{turma}')->scopeBindings()->group(function () {
            Route::get('agendas/create', [AgendaFixaController::class, 'create'])
                ->name('cursos.turmas.agendas.create')
                ->middleware('can:create,App\Models\AgendaFixa,curso');

            Route::post('agendas', [AgendaFixaController::class, 'store'])
                ->name('cursos.turmas.agendas.store')
                ->middleware('can:create,App\Models\AgendaFixa,curso');

            Route::delete('agendas/{agenda}', [AgendaFixaController::class, 'destroy'])
                ->name('cursos.turmas.agendas.destroy')
                ->middleware('can:delete,agenda');
        });
    });
});

// Rotas protegidas - apenas para admin global
Route::prefix('admin')->middleware(['auth', 'verified', 'admin'])->name('admin.')->group(function () {
    Route::resource('cursos', CursoController::class);

    Route::prefix('cursos/{curso}')->group(function () {
        Route::resource('turmas', TurmaController::class)->except(['index', 'show', 'agenda']);

        Route::prefix('turmas/{turma}')->scopeBindings()->group(function () {
            Route::get('agendas/create', [AgendaFixaController::class, 'create'])
                ->name('turmas.agendas.create');

            Route::post('agendas', [AgendaFixaController::class, 'store'])
                ->name('turmas.agendas.store');

            Route::delete('agendas/{agenda}', [AgendaFixaController::class, 'destroy'])
                ->name('turmas.agendas.destroy');
        });
    });
});


// Rotas para agenda fixa (acessível a todos autenticados)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/agenda-fixa', function () {
        $agendas = AgendaFixa::with(['user', 'disciplina'])->get();
        $trocasAtivas = TrocaAgenda::realmenteAtivas()
            ->with([
                'agendaOriginal.disciplina',
                'agendaOriginal.user',
                'agendaDesejada.disciplina',
                'agendaDesejada.user'
            ])
            ->get();

        return Inertia::render('AgendaFixa/List', [
            'agendas' => $agendas,
            'trocasAtivas' => $trocasAtivas
        ]);
    })->name('agenda-fixa.index');
});

// Rotas de administração
Route::prefix('admin')->middleware(['auth', 'verified', 'admin'])->name('admin.')->group(function () {
    // Gerenciamento de usuários
    Route::resource('users', UserController::class)->except(['show']);
    Route::post('users/{user}/upload-photo', [UserController::class, 'uploadPhoto'])->name('users.uploadPhoto');
    Route::get('/users/inactive', [UserController::class, 'inactive'])
        ->name('users.inactive');
    Route::put('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');

    // Gerenciamento de disciplinas
    Route::resource('disciplinas', DisciplinaController::class);

    // Gerenciamento de agenda fixa
    Route::resource('agenda-fixa', AgendaFixaController::class);

    Route::get('/agenda-fixa/create/{turma?}', [AgendaFixaController::class, 'create'])
        ->name('agenda-fixa.create');

    // Gerenciamento de trocas de agenda
    Route::get('/trocas-agenda', [TrocaAgendaController::class, 'adminIndex'])->name('trocas-agenda.index');
    Route::put('/trocas-agenda/{id}/aprovar', [TrocaAgendaController::class, 'aprovar'])->name('trocas-agenda.aprovar');
    Route::put('/trocas-agenda/{id}/rejeitar', [TrocaAgendaController::class, 'rejeitar'])->name('trocas-agenda.rejeitar');
});

// Rotas para professores (trocas de agenda)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('troca-agendas', TrocaAgendaController::class)->only(['index', 'create', 'store']);
});
