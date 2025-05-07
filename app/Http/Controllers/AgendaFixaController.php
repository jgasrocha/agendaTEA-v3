<?php

namespace App\Http\Controllers;

use App\Models\AgendaFixa;
use App\Models\Curso;
use App\Models\Disciplina;
use App\Models\User;
use App\Models\TrocaAgenda;
use App\Models\Turma;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AgendaFixaController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    use AuthorizesRequests;
    public function index(Turma $turma)
    {
        $agendas = AgendaFixa::with(['disciplina', 'user'])->where('turma_id', $turma->id)->get();
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
     * Helper method to get active swaps
     */
    protected function getTrocasAtivas()
    {
        $trocas = TrocaAgenda::with([
            'agendaOriginal.disciplina',
            'agendaOriginal.user',
            'agendaDesejada.disciplina',
            'agendaDesejada.user'
        ])
            ->realmenteAtivas()
            ->get();

        Log::info('Trocas ativas encontradas:', [
            'total' => $trocas->count(),
            'trocas' => $trocas->map(function ($troca) {
                return [
                    'id' => $troca->id,
                    'data_inicio' => $troca->data_inicio,
                    'data_fim' => $troca->data_fim,
                    'status' => $troca->status,
                    'original' => $troca->agenda_original_id,
                    'desejada' => $troca->agenda_desejada_id
                ];
            })
        ]);

        return $trocas->map(function ($troca) {
            $troca->estaAtiva = true;
            return $troca;
        });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Curso $curso, Turma $turma)
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Apenas administradores podem criar aulas');
        }

        $professoresAssociados = AgendaFixa::whereHas('turma', function($query) use ($curso) {
            $query->where('curso_id', $curso->id);
        })
        ->get()
        ->pluck('user_id', 'disciplina_id');

        return Inertia::render('AgendaFixa/Create', [
            'disciplinas' => Disciplina::whereHas('cursos', function ($query) use ($curso) {
                $query->where('cursos.id', $curso->id);
            })->get(),
            'users' => User::all(),
            'turma' => $turma->load('curso'), // Carrega o curso relacionado
            'curso' => $curso, // Passa o curso separadamente também
            'professoresAssociados' => $professoresAssociados
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'disciplina_id' => 'required|exists:disciplinas,id',
            'dia_semana' => 'required|integer|min:1|max:6',
            'bloco' => 'required|integer|min:1|max:8',
            'turno' => 'required|in:manhã,tarde',
            'turma_id' => 'required|exists:turmas,id',
            'curso_id' => 'required|exists:cursos,id'
        ]);

        $existingAgenda = AgendaFixa::where('disciplina_id', $request->disciplina_id)
            ->whereHas('turma', function ($query) use ($request) {
                $query->where('curso_id', $request->curso_id);
            })
            ->where('user_id', '!=', $request->user_id)
            ->first();

        if ($existingAgenda) {
            return back()->withErrors([
                'disciplina_id' => 'Esta disciplina já está associada ao professor ' .
                    $existingAgenda->user->name .
                    ' neste curso. Escolha o mesmo professor ou outra disciplina.'
            ]);
        }

        $user = User::find($request->user_id);
        $userPhoto = $user ? $user->photo : null;

        AgendaFixa::create([
            'user_id' => $request->user_id,
            'disciplina_id' => $request->disciplina_id,
            'dia_semana' => $request->dia_semana,
            'bloco' => $request->bloco,
            'turno' => $request->turno,
            'user_photo' => $userPhoto,
            'turma_id' => $request->turma_id,
        ]);

        return redirect()->route('cursos.turmas.agenda.index', [
            'curso' => $request->curso_id,
            'turma' => $request->turma_id
        ])
            ->with('success', 'Agenda Criada com Sucesso');
    }

    /**
     * Display the specified resource.
     */
    public function show(AgendaFixa $agendaFixa)
    {
        $agendaFixa->load(['disciplina', 'user']);
        $trocasRelacionadas = TrocaAgenda::where('agenda_original_id', $agendaFixa->id)
            ->orWhere('agenda_desejada_id', $agendaFixa->id)
            ->with(['agendaOriginal', 'agendaDesejada'])
            ->get();

        return Inertia::render('AgendaFixa/Show', [
            'agenda' => $agendaFixa,
            'trocasRelacionadas' => $trocasRelacionadas
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AgendaFixa $agendaFixa)
    {
        $disciplinas = Disciplina::all();
        $users = User::all();

        return Inertia::render('AgendaFixa/Edit', [
            'agenda' => $agendaFixa,
            'disciplinas' => $disciplinas,
            'users' => $users
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AgendaFixa $agendaFixa)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'disciplina_id' => 'required|exists:disciplinas,id',
            'dia_semana' => 'required|integer|min:1|max:6',
            'bloco' => 'required|integer|min:1|max:8',
            'turno' => 'required|in:manhã,tarde',
            'user_photo' => 'nullable|string', // Alterado para string pois a foto vem do usuário
        ]);

        $agendaFixa->update($request->all());

        return redirect()->route('cursos.turmas.agenda.index')
            ->with('success', 'Agenda Atualizada com Sucesso');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $cursoId, $turmaId, $agendaId)
    {
        $agendaFixa = AgendaFixa::findOrFail($agendaId);
        $this->authorize('delete', $agendaFixa); // Usando a policy corretamente
        DB::beginTransaction();

        try {
            // Verifica trocas pendentes
            $trocasRelacionadas = TrocaAgenda::where(function ($query) use ($agendaFixa) {
                $query->where('agenda_original_id', $agendaFixa->id)
                    ->orWhere('agenda_desejada_id', $agendaFixa->id);
            })->get();

            if ($trocasRelacionadas->where('status', 'pendente')->isNotEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível deletar esta agenda pois existem trocas pendentes relacionadas a ela.'
                ], 403);
            }

            // Remove trocas ativas e agendas parceiras
            $trocasAtivas = $trocasRelacionadas->where('status', 'aceita');
            foreach ($trocasAtivas as $troca) {
                $agendaParceiraId = $troca->agenda_original_id == $agendaFixa->id
                    ? $troca->agenda_desejada_id
                    : $troca->agenda_original_id;

                if ($agendaParceira = AgendaFixa::find($agendaParceiraId)) {
                    $agendaParceira->delete();
                }
                $troca->delete();
            }

            $agendaFixa->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aula removida com sucesso!',
                'agendas' => AgendaFixa::where('turma_id', $turmaId)->get()
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar agenda: ' . $e->getMessage()
            ], 500);
        }
    }
}
