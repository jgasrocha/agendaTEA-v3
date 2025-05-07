<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrocaAgenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'agenda_original_id',
        'agenda_desejada_id',
        'status',
        'data_inicio',
        'data_fim',
        'disciplina_original_id',
        'disciplina_desejada_id',
        'turma_id'
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agendaOriginal()
    {
        return $this->belongsTo(AgendaFixa::class, 'agenda_original_id');
    }

    public function agendaDesejada()
    {
        return $this->belongsTo(AgendaFixa::class, 'agenda_desejada_id');
    }

    public function scopeRealmenteAtivas($query)
    {
        $now = now()->setTimezone(config('app.timezone'));
        $dateString = $now->format('Y-m-d');
        $timeString = $now->format('H:i:s');

        return $query->where('status', 'aceita')
            ->whereDate('data_inicio', '<=', $dateString)
            ->where(function ($q) use ($dateString, $timeString) {
                $q->whereDate('data_fim', '>', $dateString)
                    ->orWhere(function ($q2) use ($dateString, $timeString) {
                        $q2->whereDate('data_fim', $dateString)
                            ->whereHas('agendaDesejada', function ($q3) use ($timeString) {
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

                                $q3->where(function ($q4) use ($timeString, $horariosFim) {
                                    foreach ($horariosFim as $bloco => $horaFim) {
                                        $q4->orWhere(function ($q5) use ($bloco, $timeString, $horaFim) {
                                            $q5->where('bloco', $bloco)
                                                ->whereRaw("time(?) <= time(?)", [$timeString, $horaFim]);
                                        });
                                    }
                                });
                            });
                    });
            });
    }
}
