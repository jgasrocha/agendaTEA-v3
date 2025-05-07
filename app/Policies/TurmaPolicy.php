<?php

namespace App\Policies;

use App\Models\AgendaFixa;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TurmaPolicy
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
    public function view(?User $user = null, Turma $turma): bool
    {
        return true;
    }

    public function edit(User $user, Turma $turma)
    {
        return $user->is_admin || $user->id === $turma->user_id;
    }
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Turma $turma): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Turma $turma,AgendaFixa $agendaFixa): bool
    {
        return $user->is_admin || $user->can('edit', $agendaFixa->turma);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Turma $turma): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Turma $turma): bool
    {
        return false;
    }
}
