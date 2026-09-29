<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

/**
 * Règles d'accès aux demandes (plaintes et bugs).
 */
class TicketPolicy
{
    /**
     * Tout utilisateur connecté peut consulter la liste (filtrée par scope).
     */
    public function viewAny(User $utilisateur): bool
    {
        return true;
    }

    /**
     * Un agent de juridiction ne voit que les demandes de sa structure.
     */
    public function view(User $utilisateur, Ticket $ticket): bool
    {
        return $ticket->visiblePar($utilisateur);
    }

    public function create(User $utilisateur): bool
    {
        return ! $utilisateur->estAdministrateur() || true;
    }

    /**
     * La modification du contenu est réservée au support.
     */
    public function update(User $utilisateur, Ticket $ticket): bool
    {
        return $utilisateur->estInterne();
    }

    public function delete(User $utilisateur, Ticket $ticket): bool
    {
        return $utilisateur->estAdministrateur();
    }

    public function affecter(User $utilisateur, Ticket $ticket): bool
    {
        return $utilisateur->estInterne();
    }

    public function changerStatut(User $utilisateur, Ticket $ticket): bool
    {
        return $utilisateur->estInterne();
    }

    public function resoudre(User $utilisateur, Ticket $ticket): bool
    {
        return $utilisateur->estInterne() && $ticket->estOuvert();
    }

    public function rejeter(User $utilisateur, Ticket $ticket): bool
    {
        return $utilisateur->estInterne() && $ticket->estOuvert();
    }

    /**
     * Le support clôture, ou la juridiction confirme la résolution.
     */
    public function cloturer(User $utilisateur, Ticket $ticket): bool
    {
        if ($utilisateur->estInterne()) {
            return true;
        }

        return $ticket->declarant_id === $utilisateur->id;
    }

    public function commenter(User $utilisateur, Ticket $ticket): bool
    {
        return $ticket->visiblePar($utilisateur);
    }

    /**
     * On n'ajoute des pièces que sur une demande encore ouverte.
     */
    public function ajouterPieceJointe(User $utilisateur, Ticket $ticket): bool
    {
        return $ticket->visiblePar($utilisateur) && $ticket->estOuvert();
    }

    /**
     * Suppression d'une pièce jointe : son auteur ou le support.
     */
    public function supprimerPieceJointe(User $utilisateur, Ticket $ticket): bool
    {
        return $utilisateur->estInterne() || $ticket->declarant_id === $utilisateur->id;
    }
}
