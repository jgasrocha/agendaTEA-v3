<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CursoController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        return Inertia::render('Cursos/Index', [
            'cursos' => Curso::withCount(['turmas', 'disciplinas'])
                            ->with(['admins' => function($query) {
                                $query->select('users.id', 'name', 'email', 'is_admin');
                            }])
                            ->get(),
            'usuarios' => User::where('active', true)->get(['id', 'name', 'email', 'is_admin']),
            'auth' => ['user' => Auth::user()]
        ]);
    }

    public function create()
    {
        return Inertia::render('Cursos/Create', [
            'usuarios' => User::where('active', true)->get(['id', 'name', 'email'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'imagem' => 'nullable|image|max:2048',
            'admin_id' => 'nullable|exists:users,id'
        ]);

        if ($request->hasFile('imagem')) {
            $validated['imagem'] = $request->file('imagem')->store('cursos', 'public');
        }

        $curso = Curso::create($validated);

        $admins = [];
        if ($request->admin_id) {
            $admins[] = $request->admin_id;
        }
        if (Auth::user()->is_admin) {
            $admins[] = Auth::id();
        }
        
        $curso->admins()->sync(array_unique($admins));

        return redirect()->route('admin.cursos.index')->with('success', 'Curso criado com sucesso!');
    }

    public function addAdmin(Request $request, Curso $curso, User $user)
    {
        $this->authorize('manageAdmins', $curso);
        $curso->addAdmin($user);
        return back()->with('success', 'Administrador adicionado com sucesso!');
    }

    public function removeAdmin(Request $request, Curso $curso, User $user)
    {
        $this->authorize('manageAdmins', $curso);
        
        if ($user->id === Auth::id() && $curso->admins()->count() === 1) {
            return back()->with('error', 'Você não pode se remover como único administrador!');
        }

        $curso->removeAdmin($user);
        return back()->with('success', 'Administrador removido com sucesso!');
    }

    public function show(Curso $curso)
    {
        return Inertia::render('Cursos/Show', [
            'curso' => $curso->load(['turmas', 'disciplinas', 'admins'])
        ]);
    }

    public function edit(Curso $curso)
    {
        return Inertia::render('Cursos/Edit', [
            'curso' => $curso->load('admins'),
            'usuarios' => User::where('active', true)->get(['id', 'name', 'email'])
        ]);
    }

    public function update(Request $request, Curso $curso)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'imagem' => 'nullable|image|max:2048',
            'admin_id' => 'nullable|exists:users,id'
        ]);

        if ($request->hasFile('imagem')) {
            if ($curso->imagem) {
                Storage::disk('public')->delete($curso->imagem);
            }
            $validated['imagem'] = $request->file('imagem')->store('cursos', 'public');
        }

        $curso->update($validated);

        if ($request->has('admin_id')) {
            $admins = [$request->admin_id];
            
            // Mantém o admin global se não for o mesmo
            if (Auth::user()->is_admin && !in_array(Auth::id(), $admins)) {
                $admins[] = Auth::id();
            }
            
            $curso->admins()->sync(array_unique($admins));
        }

        return redirect()->route('admin.cursos.index')->with('success', 'Curso atualizado com sucesso!');
    }

    public function destroy(Curso $curso)
    {
        if ($curso->imagem) {
            Storage::disk('public')->delete($curso->imagem);
        }

        $curso->delete();
        return redirect()->route('admin.cursos.index')->with('success', 'Curso removido com sucesso!');
    }
}