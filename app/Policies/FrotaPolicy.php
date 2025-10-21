<?php

namespace App\Policies;

use App\Models\Frota;
use App\Models\User;

class FrotaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Frotas']);
    }

    public function view(User $user, Frota $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Frotas']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Frotas']);
    }

    public function update(User $user, Frota $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Frotas']);
    }

    public function delete(User $user, Frota $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function restore(User $user, Frota $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function forceDelete(User $user, Frota $model): bool
    {
        return $user->hasRole('Administrador');
    }
}