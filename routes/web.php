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
use App\Models\AgendaFixa;
use App\Models\Curso;
use App\Models\TrocaAgenda;
use Illuminate\Support\Facades\Auth;

// Rotas públicas
Route::get('/', function () {
    $cursos = Curso::all();

    return Inertia::render('Cursos/Index', [
        'cursos' => $cursos,
        'auth' => Auth::user() ? ['user' => Auth::user()] : null
    ]);
})->name('home');

Route::get('/cursos/{curso}/turmas/{turma}/agenda', [TurmaController::class, 'agenda'])
    ->name('cursos.turmas.agenda.index');

Route::resource('cursos', CursoController::class)
    ->middleware(['auth', 'verified']);

// Rotas para Turmas (aninhadas em Cursos)



// Rotas de autenticação
require __DIR__ . '/auth.php';

Route::prefix('admin')->middleware(['auth', 'verified', 'admin'])->name('admin.')->group(function () {
    Route::resource('cursos', CursoController::class);
    
    // Rotas aninhadas para Turmas e Aulas
    Route::prefix('cursos/{curso}')->group(function () {
        Route::resource('turmas', TurmaController::class)->names('cursos.turmas');
    
        Route::prefix('turmas/{turma}')->scopeBindings()->group(function () {
            Route::get('agenda', [TurmaController::class, 'agenda'])->name('cursos.turmas.agenda.index');
            Route::get('agendas/create', [AgendaFixaController::class, 'create'])->name('cursos.turmas.agendas.create');
            Route::post('agendas', [AgendaFixaController::class, 'store'])->name('cursos.turmas.agendas.store');
            Route::delete('agendas/{agenda}', [AgendaFixaController::class, 'destroy'])->name('cursos.turmas.agendas.destroy');
        });
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('cursos', CursoController::class);
    
    // Rotas aninhadas para Turmas e Aulas
    Route::prefix('cursos/{curso}')->group(function () {
        Route::resource('turmas', TurmaController::class)->names('cursos.turmas');
    
        Route::prefix('turmas/{turma}')->scopeBindings()->group(function () {
            Route::get('agenda', [TurmaController::class, 'agenda'])->name('cursos.turmas.agenda.index');
            Route::get('agendas/create', [AgendaFixaController::class, 'create'])->name('cursos.turmas.agendas.create');
            Route::post('agendas', [AgendaFixaController::class, 'store'])->name('cursos.turmas.agendas.store');
            Route::delete('agendas/{agenda}', [AgendaFixaController::class, 'destroy'])->name('cursos.turmas.agendas.destroy')->middleware('can:delete,agendaFixa')
            ->scopeBindings();
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
