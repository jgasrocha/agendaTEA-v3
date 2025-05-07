<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaFixa extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'disciplina_id',
        'dia_semana',
        'bloco',
        'turno',
        'user_photo',
        'turma_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function disciplina()
    {
        return $this->belongsTo(Disciplina::class);
    }

    public function turma()
    {
        return $this->belongsTo(Turma::class);
    }

    public function proximaData()
    {
        $hoje = now()->startOfDay();
        $diaAula = $this->dia_semana;

        if ($hoje->dayOfWeek <= $diaAula) {
            $dataProxima = $hoje->copy()->next($diaAula);
            if ($hoje->dayOfWeek === $diaAula) {
                return $hoje;
            }
            return $dataProxima;
        }

        return $hoje->copy()->next($diaAula);
    }
}
