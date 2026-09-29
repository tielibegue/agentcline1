<?php

namespace App\Policies;

use App\Models\Juridiction;
use App\Models\User;

/**
 * Les juridictions sont gérées exclusivement par l'administration.
 */
class JuridictionPolicy
{
    public function viewAny(User $utilisateur): bool
    {
        return $utilisateur->estInterne();
    }

    public function view(User $utilisateur, Juridiction $juridiction): bool
    {
        return $utilisateur->estInterne();
    }

    public function create(User $utilisateur): bool
    {
        return $utilisateur->estAdministrateur();
    }

    public function update(User $utilisateur, Juridiction $juridiction): bool
    {
        return $utilisateur->estAdministrateur();
    }

    public function delete(User $utilisateur, Juridiction $juridiction): bool
    {
        return $utilisateur->estAdministrateur();
    }
}
