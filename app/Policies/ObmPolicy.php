<?php

namespace App\Policies;

use App\Models\Obm;
use App\Models\User;

class ObmPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Gerente', 'Recursos Humanos', 'Frotas', 'Fornecedor']);
    }

    public function view(User $user, Obm $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Gerente', 'Recursos Humanos', 'Frotas', 'Fornecedor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Gerente']);
    }

    public function update(User $user, Obm $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Gerente', 'Recursos Humanos', 'Frotas']);
    }

    public function delete(User $user, Obm $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function restore(User $user, Obm $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function forceDelete(User $user, Obm $model): bool
    {
        return $user->hasRole('Administrador');
    }

    // Granularidade por seção
    public function viewFuncionarios(User $user, Obm $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Gerente', 'Recursos Humanos']);
    }

    public function viewVeiculos(User $user, Obm $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Gerente', 'Frotas']);
    }

    public function viewFornecedores(User $user, Obm $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Gerente', 'Fornecedor']);
    }
}