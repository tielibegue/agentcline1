<?php

namespace App\Policies;

use App\Models\User;

/**
 * Les comptes utilisateurs sont gérés par l'administration.
 */
class UserPolicy
{
    public function viewAny(User $utilisateur): bool
    {
        return $utilisateur->estAdministrateur();
    }

    public function view(User $utilisateur, User $cible): bool
    {
        return $utilisateur->estAdministrateur();
    }

    public function create(User $utilisateur): bool
    {
        return $utilisateur->estAdministrateur();
    }

    public function update(User $utilisateur, User $cible): bool
    {
        return $utilisateur->estAdministrateur();
    }

    /**
     * Un administrateur ne peut pas supprimer son propre compte.
     */
    public function delete(User $utilisateur, User $cible): bool
    {
        return $utilisateur->estAdministrateur() && $utilisateur->id !== $cible->id;
    }
}
