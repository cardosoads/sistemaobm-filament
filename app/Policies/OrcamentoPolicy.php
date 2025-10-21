<?php

namespace App\Policies;

use App\Models\Orcamento;
use App\Models\User;

class OrcamentoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Gerente', 'Orçamento']);
    }

    public function view(User $user, Orcamento $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Gerente', 'Orçamento']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Gerente', 'Orçamento']);
    }

    public function update(User $user, Orcamento $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Gerente', 'Orçamento']);
    }

    public function delete(User $user, Orcamento $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Gerente', 'Orçamento']);
    }
}