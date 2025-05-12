<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'descricao', 'imagem'];

    public function turmas()
    {
        return $this->hasMany(Turma::class);
    }

    public function disciplinas()
    {
        return $this->belongsToMany(Disciplina::class);
    }

    public function admins()
    {
        return $this->belongsToMany(User::class, 'curso_user_pivot');
    }

    public function scopeWithAdmins($query)
    {
        return $query->with(['admins' => function ($q) {
            $q->select('users.id', 'name', 'email');
        }]);
    }

    public function addAdmin(User $user)
    {
        return $this->admins()->syncWithoutDetaching([$user->id]);
    }

    public function removeAdmin(User $user)
    {
        return $this->admins()->detach($user->id);
    }
}
