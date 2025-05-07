<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CursoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Cursos/Index',[
            'cursos' => Curso::withCount(['turmas', 'disciplinas'])->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Cursos/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'imagem' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('imagem')) {
            $validated['imagem'] = $request->file('imagem')->store('cursos', 'public');
        }

        Curso::create($validated);

        return redirect()->route('cursos.index')->with('success', 'Curso criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Curso $curso)
    {
        return Inertia::render('Cursos/Show',[
            'curso' => $curso->load(['turmas', 'disciplinas'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Curso $curso)
    {
        return Inertia::render('Cursos/Edit',[
            'curso' => $curso
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Curso $curso)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'imagem' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('imagem')) {
            // Remove a imagem antiga se existir
            if ($curso->imagem) {
                Storage::disk('public')->delete($curso->imagem);
            }
            $validated['imagem'] = $request->file('imagem')->store('cursos', 'public');
        }

        $curso->update($validated);

        return redirect()->route('cursos.index')->with('success', 'Curso atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Curso $curso)
    {
        if ($curso->imagem) {
            Storage::disk('public')->delete($curso->imagem);
        }

        $curso->delete();

        return redirect()->route('cursos.index')->with('success', 'Curso removido com sucesso!');
    }
}
