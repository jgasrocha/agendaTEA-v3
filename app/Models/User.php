<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'is_admin',
        'active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean'
        ];
    }

    public function disciplinas()
    {
        return $this->hasMany(Disciplina::class);
    }

    public function cursosAdmin()
    {
        return $this->belongsToMany(Curso::class, 'curso_user_pivot');
    }

    public function isCourseAdmin(Curso $curso)
    {
        return $this->is_admin || $this->cursosAdmin()->where('curso_id', $curso->id)->exists();
    }

    public function canManageCourse(Curso $curso)
    {
        return $this->is_admin || $this->isCourseAdmin($curso);
    }
}
