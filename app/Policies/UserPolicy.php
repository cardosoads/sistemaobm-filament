<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Recursos Humanos']);
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasAnyRole(['Administrador', 'Recursos Humanos']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Recursos Humanos']);
    }

    public function update(User $user, User $model): bool
    {
        // Usuários não podem alterar a própria role para evitar auto-elevação
        if ($user->id === $model->id) {
            return false;
        }
        
        return $user->hasAnyRole(['Administrador', 'Recursos Humanos']);
    }

    public function delete(User $user, User $model): bool
    {
        // Impedir exclusão do próprio usuário
        if ($user->id === $model->id) {
            return false;
        }
        
        return $user->hasRole('Administrador');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('Administrador');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasRole('Administrador');
    }
}