<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turma extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'curso_id', 'semestre'];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function agendas()
    {
        return $this->hasMany(AgendaFixa::class);
    }

}
