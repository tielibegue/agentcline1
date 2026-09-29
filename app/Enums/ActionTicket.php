<?php

namespace App\Enums;

/**
 * Actions journalisées dans l'historique d'une demande.
 */
enum ActionTicket: string
{
    case CREATION = 'CREATION';
    case MODIFICATION = 'MODIFICATION';
    case AFFECTATION = 'AFFECTATION';
    case CHANGEMENT_STATUT = 'CHANGEMENT_STATUT';
    case RESOLUTION = 'RESOLUTION';
    case REJET = 'REJET';
    case CLOTURE = 'CLOTURE';
    case COMMENTAIRE = 'COMMENTAIRE';
    case PIECE_AJOUTEE = 'PIECE_AJOUTEE';
    case PIECE_SUPPRIMEE = 'PIECE_SUPPRIMEE';

    public function label(): string
    {
        return match ($this) {
            self::CREATION => 'Déclaration de la demande',
            self::MODIFICATION => 'Modification de la demande',
            self::AFFECTATION => 'Affectation à un agent',
            self::CHANGEMENT_STATUT => 'Changement de statut',
            self::RESOLUTION => 'Résolution de la demande',
            self::REJET => 'Rejet de la demande',
            self::CLOTURE => 'Clôture de la demande',
            self::COMMENTAIRE => 'Nouveau commentaire',
            self::PIECE_AJOUTEE => 'Pièce jointe ajoutée',
            self::PIECE_SUPPRIMEE => 'Pièce jointe supprimée',
        };
    }

    public function couleur(): string
    {
        return match ($this) {
            self::CREATION => 'info',
            self::MODIFICATION => 'neutral',
            self::AFFECTATION => 'purple',
            self::CHANGEMENT_STATUT => 'warning',
            self::RESOLUTION => 'success',
            self::REJET => 'danger',
            self::CLOTURE => 'dark',
            self::COMMENTAIRE => 'info',
            self::PIECE_AJOUTEE => 'purple',
            self::PIECE_SUPPRIMEE => 'neutral',
        };
    }
}
