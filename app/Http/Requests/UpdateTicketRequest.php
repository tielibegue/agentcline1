<?php

namespace App\Http\Requests;

/**
 * Mise à jour du contenu d'une demande par le support.
 */
class UpdateTicketRequest extends StoreTicketRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $regles = parent::rules();

        // En modification, aucune pièce jointe n'est téléversée depuis ce formulaire.
        unset($regles['pieces_jointes'], $regles['pieces_jointes.*']);
        unset($regles['juridiction_id']);

        return $regles;
    }
}
