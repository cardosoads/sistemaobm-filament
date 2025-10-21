<?php

namespace App\Policies;

use App\Models\Veiculo;
use App\Models\User;

class VeiculoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Frotas']);
    }

    public function view(User $user, Veiculo $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Frotas']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Frotas']);
    }

    public function update(User $user, Veiculo $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Frotas']);
    }

    public function delete(User $user, Veiculo $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function restore(User $user, Veiculo $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function forceDelete(User $user, Veiculo $model): bool
    {
        return $user->hasRole('Administrador');
    }
}