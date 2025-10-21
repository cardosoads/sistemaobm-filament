<?php

namespace App\Policies;

use App\Models\Colaborador;
use App\Models\User;

class ColaboradorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Recursos Humanos']);
    }

    public function view(User $user, Colaborador $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Recursos Humanos']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Recursos Humanos']);
    }

    public function update(User $user, Colaborador $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Recursos Humanos']);
    }

    public function delete(User $user, Colaborador $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function restore(User $user, Colaborador $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function forceDelete(User $user, Colaborador $model): bool
    {
        return $user->hasRole('Administrador');
    }
}