<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Disciplina extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'foto'];

    public function cursos()
    {
        return $this->belongsToMany(Curso::class, 'curso_disciplina')
            ->withTimestamps();
    }

    public function turmas()
    {
        return $this->belongsToMany(Turma::class, 'agenda_fixas', 'disciplina_id', 'turma_id');
    }
}
