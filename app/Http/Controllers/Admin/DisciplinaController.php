<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Disciplina;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DisciplinaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cursoId = $request->query('curso_id');

        $query = Curso::with(['disciplinas' => function($query) {
            $query->orderBy('nome');
        }]);

        if ($cursoId) {
            $query->where('id', $cursoId);
        }

        return Inertia::render('Admin/Disciplinas/DisciplinaList', [
            'cursos' => $query->get(),
            'current_curso_id' => $cursoId
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Disciplinas/CreateDisciplina', [
            'cursos' => Curso::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'cursos_ids' => 'required|array',
            'cursos_ids.*' => 'exists:cursos,id',
            'foto' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('disciplinas', 'public');
        }

        $disciplina = Disciplina::create($data);
        $disciplina->cursos()->sync($request->cursos_ids);

        return redirect()->route('admin.disciplinas.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Disciplina $disciplina, Request $request)
    {
        $cursoId = $request->query('curso_id');

        return Inertia::render('Admin/Disciplinas/Edit', [
            'disciplina' => $disciplina->load(['cursos']),
            'cursos' => Curso::all(),
            'current_curso_id' => $cursoId,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Disciplina $disciplina)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'cursos_ids' => 'required|array',
            'cursos_ids.*' => 'exists:cursos,id',
            'foto' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('disciplinas', 'public');
        }

        $disciplina->update($data);
        $disciplina->cursos()->sync($request->cursos_ids);

        return redirect()->route('admin.disciplinas.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Disciplina $disciplina)
    {
        $disciplina->delete();

        return redirect()->route('admin.disciplinas.index');
    }
}
