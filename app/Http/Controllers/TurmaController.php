<?php

namespace App\Http\Controllers;

use App\Models\AgendaFixa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Curso;
use App\Models\TrocaAgenda;
use App\Models\Turma;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TurmaController extends Controller
{
    use AuthorizesRequests;
    public function index(Curso $curso)
    {
        return Inertia::render('Turmas/Index', [
            'curso' => $curso,
            'turmas' => $curso->turmas()->withCount('agendas')->get()
        ]);
    }

    public function agenda(Curso $curso, Turma $turma)
    {
        $this->authorize('view', $turma);
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

        return Inertia::render('Turmas/Agenda/Index', [
            'turma' => $turma,
            'agendas' => $agendas,
            'trocasAtivas' => $trocasAtivas,
            'canEdit' => Gate::allows('edit', $turma)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Curso $curso)
    {
        return Inertia::render('Turmas/Create', [
            'curso' => $curso
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Curso $curso)
    {
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
            'turma' => $turma->load(['agendas.disciplina', 'agendas.user'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Curso $curso, Turma $turma)
    {
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
        $turma->delete();

        return redirect()->route('cursos.turmas.index', $curso)
            ->with('success', 'Turma removida com sucesso!');
    }
}
