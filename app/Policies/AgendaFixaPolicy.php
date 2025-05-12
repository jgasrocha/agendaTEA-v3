<?php

namespace App\Policies;

use App\Models\AgendaFixa;
use App\Models\Curso;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AgendaFixaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AgendaFixa $agendaFixa): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, $curso): bool
    {
        if (!$curso instanceof Curso) {
            $curso = Curso::findOrFail($curso);
        }
        return $user->is_admin || $user->isCourseAdmin($curso);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Curso $curso, AgendaFixa $agendaFixa): bool
    {
        return $user->is_admin || $user->isCourseAdmin($curso);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, $agenda): bool
    {
        if (!$agenda instanceof AgendaFixa) {
            $agenda = AgendaFixa::with('turma.curso')->findOrFail($agenda);
        }

        // Carrega as relações se não estiverem carregadas
        if (!$agenda->relationLoaded('turma.curso')) {
            $agenda->load('turma.curso');
        }

        $curso = $agenda->turma->curso;

        return $user->is_admin || $user->isCourseAdmin($curso);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AgendaFixa $agendaFixa): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AgendaFixa $agendaFixa): bool
    {
        return false;
    }
}
