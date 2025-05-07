<?php

namespace App\Http\Controllers;

use App\Models\AgendaFixa;
use App\Models\TrocaAgenda;
use Carbon\Carbon;
use Exception;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB as FacadesDB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class TrocaAgendaController extends Controller
{
    public function index(Request $request)
    {
        // Para usuário comum: conta apenas as do usuário
        $pendingRequestsCount = Auth::user()->is_admin
            ? TrocaAgenda::where('status', 'pendente')->count()
            : TrocaAgenda::where('user_id', Auth::id())
            ->where('status', 'pendente')
            ->count();

        // Carrega as trocas conforme o tipo de usuário
        $trocas = Auth::user()->is_admin
            ? TrocaAgenda::with(['user', 'agendaOriginal.disciplina', 'agendaDesejada.disciplina'])->get()
            : TrocaAgenda::with(['agendaOriginal.disciplina', 'agendaDesejada.disciplina'])
            ->where('user_id', Auth::id())
            ->get();

        return Inertia::render('TrocaAgenda/List', [
            'auth' => [
                'user' => $request->user(),
            ],
            'trocas' => $trocas,
            'pendingRequests' => $pendingRequestsCount,
        ]);
    }

    public function adminIndex()
    {
        $trocas = TrocaAgenda::with([
            'user' => function ($query) {
                $query->select('id', 'name', 'photo');
            },
            'agendaOriginal.disciplina',
            'agendaOriginal.user',
            'agendaDesejada.disciplina',
            'agendaDesejada.user'
        ])->latest()->get();

        return Inertia::render('Admin/TrocasAgenda/Index', [
            'trocas' => $trocas,
        ]);
    }

    public function create()
    {
        $agendas = AgendaFixa::where('user_id', Auth::id())->get();

        return Inertia::render('TrocaAgenda/Create', [
            'agendas' => $agendas,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'agenda_original_id' => 'required|exists:agenda_fixas,id',
            'agenda_desejada_id' => 'required|exists:agenda_fixas,id',
            'disciplina_original_id' => 'required|exists:disciplinas,id',
            'disciplina_desejada_id' => 'required|exists:disciplinas,id',
            'turma_id' => 'sometimes|exists:turmas,id'
        ]);

        TrocaAgenda::create([
            'user_id' => Auth::id(),
            'agenda_original_id' => $request->agenda_original_id,
            'agenda_desejada_id' => $request->agenda_desejada_id,
            'disciplina_original_id' => $request->disciplina_original_id,
            'disciplina_desejada_id' => $request->disciplina_desejada_id,
            'status' => 'pendente',
            'turma_id' => $request->turma_id
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Solicitação de troca enviada com sucesso.'
            ]);
        }

        return redirect()->route('troca-agendas.index')->with('success', 'Solicitação de troca enviada com sucesso.');
    }

    public function aprovar($id)
    {
        try {
            $troca = TrocaAgenda::with([
                'agendaOriginal.disciplina',
                'agendaOriginal.user',
                'agendaDesejada.disciplina',
                'agendaDesejada.user'
            ])->findOrFail($id);

            // Blocos com os horários finais da aula
            $horariosFim = [
                1 => '09:00',
                2 => '10:10',
                3 => '11:10',
                4 => '13:10',
                5 => '14:10',
                6 => '15:20',
                7 => '16:20',
                8 => '17:00'
            ];

            $dataAula = $troca->agendaDesejada->proximaData();  // método no model AgendaFixa
            $bloco = $troca->agendaDesejada->bloco ?? null;

            if (!$dataAula || !$bloco) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível calcular a data final da troca. Verifique a agenda desejada.'
                ], 400);
            }

            // Define a data final da troca com base no horário de término da aula
            $dataFim = Carbon::parse(
                $dataAula->format('Y-m-d') . ' ' . ($horariosFim[$bloco] ?? '23:59'),
                config('app.timezone')
            );

            $troca->update([
                'status' => 'aceita',
                'data_inicio' => now(),
                'data_fim' => $dataFim
            ]);

            return redirect()->route('admin.trocas-agenda.index')
                ->with('success', 'Troca aprovada com sucesso!')
                ->with('trocasAtivas', TrocaAgenda::realmenteAtivas()->get());
        } catch (Exception $e) {
            return back()->with('error', 'Erro ao aprovar troca: ' . $e->getMessage());
        }
    }

    public function rejeitar($id)
    {
        try {
            $troca = TrocaAgenda::with(['user', 'agendaOriginal', 'agendaDesejada'])
                ->findOrFail($id);

            $troca->update(['status' => 'rejeitada']);

            return back()->with([
                'success' => 'Troca rejeitada com sucesso!',
                'updatedTroca' => $troca->fresh()
            ]);
        } catch (Exception $e) {
            return back()->withErrors([
                'error' => 'Erro ao rejeitar troca: ' . $e->getMessage()
            ]);
        }
    }
}
