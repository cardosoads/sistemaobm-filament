<?php

namespace App\Policies;

use App\Models\RecursoHumano;
use App\Models\User;

class RecursoHumanoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Recursos Humanos']);
    }

    public function view(User $user, RecursoHumano $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Recursos Humanos']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Recursos Humanos']);
    }

    public function update(User $user, RecursoHumano $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Recursos Humanos']);
    }

    public function delete(User $user, RecursoHumano $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function restore(User $user, RecursoHumano $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function forceDelete(User $user, RecursoHumano $model): bool
    {
        return $user->hasRole('Administrador');
    }
}