<?php

namespace App\Policies;

use App\Models\TipoVeiculo;
use App\Models\User;

class TipoVeiculoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Frotas']);
    }

    public function view(User $user, TipoVeiculo $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Frotas']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Frotas']);
    }

    public function update(User $user, TipoVeiculo $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Frotas']);
    }

    public function delete(User $user, TipoVeiculo $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function restore(User $user, TipoVeiculo $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function forceDelete(User $user, TipoVeiculo $model): bool
    {
        return $user->hasRole('Administrador');
    }
}