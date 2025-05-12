<?php

namespace App\Http\Controllers;

use App\Models\AgendaFixa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Curso;
use App\Models\TrocaAgenda;
use App\Models\Turma;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TurmaController extends Controller
{
    public function index(Curso $curso)
    {
        $user = Auth::user();
        $isCourseAdmin = $user ? $curso->admins->contains($user) : false;
        return Inertia::render('Turmas/Index', [
            'curso' => $curso,
            'turmas' => $curso->turmas()->withCount('agendas')->get(),
            'auth' => ['user' => $user],
            'isCourseAdmin' => $isCourseAdmin
        ]);
    }

    public function agenda(Curso $curso, Turma $turma)
    {
        $agendas = AgendaFixa::with(['disciplina', 'user'])
            ->where('turma_id', $turma->id)
            ->get();

        $trocasAtivas = TrocaAgenda::realmenteAtivas()
            ->whereHas('agendaOriginal', function ($q) use ($turma) {
                $q->where('turma_id', $turma->id);
            })
            ->with([
                'agendaOriginal.disciplina',
                'agendaOriginal.user',
                'agendaDesejada.disciplina',
                'agendaDesejada.user'
            ])
            ->get();

        $user = Auth::user();
        $isCourseAdmin = $user ? $curso->admins->contains($user) : false;
        $canCreateAgenda = Auth::check() ? Gate::allows('create', [AgendaFixa::class, $curso]) : false;
        return Inertia::render('Turmas/Agenda/Index', [
            'turma' => $turma,
            'agendas' => $agendas,
            'trocasAtivas' => $trocasAtivas,
            'auth' => ['user' => Auth::user()],
            'isCourseAdmin' => $isCourseAdmin,
            'canEdit' => $canCreateAgenda
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Curso $curso)
    {
        Gate::authorize('create', [Turma::class, $curso]);
        return Inertia::render('Turmas/Create', [
            'curso' => $curso
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Curso $curso)
    {
        Gate::authorize('create', [Turma::class, $curso]);

        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'semestre' => 'required|string|regex:/^\d{4}\.[12]$/'
        ]);

        $curso->turmas()->create($validated);

        return redirect()->route('cursos.turmas.index', $curso)
            ->with('success', 'Turma criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Curso $curso, Turma $turma)
    {
        return Inertia::render('Turmas/Show', [
            'curso' => $curso,
            'turma' => $turma->load(['agendas.disciplina', 'agendas.user']),
            'canEdit' => Auth::check() ? Gate::allows('edit', $turma) : false,
            'canDelete' => Auth::check() ? Gate::allows('delete', $turma) : false
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Curso $curso, Turma $turma)
    {
        Gate::authorize('update', $turma);

        return Inertia::render('Turmas/Edit', [
            'curso' => $curso,
            'turma' => $turma
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Curso $curso, Turma $turma)
    {
        Gate::authorize('update', $turma);

        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'semestre' => 'required|string|regex:/^\d{4}\.[12]$/'
        ]);

        $turma->update($validated);

        return redirect()->route('cursos.turmas.index', $curso)
            ->with('success', 'Turma atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Curso $curso, Turma $turma)
    {
        Gate::authorize('delete', $turma);
        
        $turma->delete();

        return redirect()->route('cursos.turmas.index', $curso)
            ->with('success', 'Turma removida com sucesso!');
    }
}
