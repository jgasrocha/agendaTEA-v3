<?php

namespace App\Jobs;

use App\Models\TrocaAgenda;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReverterTrocasExpiradasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $now = Carbon::now()->setTimezone(config('app.timezone'));
        $horariosFim = [
            1 => '09:00', 2 => '10:10', 3 => '11:10',
            4 => '13:10', 5 => '14:10', 6 => '15:20',
            7 => '16:20', 8 => '17:00'
        ];

        $trocasExpiradas = TrocaAgenda::with('agendaDesejada')
            ->where('status', 'aceita')
            ->where(function($query) use ($now, $horariosFim) {
                // Trocas que já passaram da data final
                $query->where('data_fim', '<', $now)
                    ->orWhere(function($q) use ($now, $horariosFim) {
                        // Trocas que expiram hoje e já passaram do horário
                        $q->whereDate('data_fim', $now->toDateString())
                            ->whereHas('agendaDesejada', function($subQ) use ($now, $horariosFim) {
                                $subQ->where(function($q2) use ($now, $horariosFim) {
                                    foreach ($horariosFim as $bloco => $horaFim) {
                                        $q2->orWhere(function($q3) use ($bloco, $now, $horaFim) {
                                            $q3->where('bloco', $bloco)
                                               ->whereRaw('time(?) > time(?)', [
                                                   $now->format('H:i:s'),
                                                   $horaFim
                                               ]);
                                        });
                                    }
                                });
                            });
                    });
            })
            ->get();

        foreach ($trocasExpiradas as $troca) {
            try {
                $troca->update(['status' => 'expirada']);
                Log::info("Troca expirada ID {$troca->id}", [
                    'bloco' => $troca->agendaDesejada->bloco,
                    'horario_fim' => $horariosFim[$troca->agendaDesejada->bloco] ?? null
                ]);
            } catch (Exception $e) {
                Log::error("Erro ao expirar troca ID {$troca->id}: " . $e->getMessage());
            }
        }
    }
}