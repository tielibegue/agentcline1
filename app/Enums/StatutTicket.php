<?php

namespace App\Enums;

/**
 * Cycle de vie d'une demande : de la déclaration à la résolution.
 */
enum StatutTicket: string
{
    case NOUVELLE = 'NOUVELLE';
    case ASSIGNEE = 'ASSIGNEE';
    case EN_COURS = 'EN_COURS';
    case EN_ATTENTE_JURIDICTION = 'EN_ATTENTE_JURIDICTION';
    case RESOLUE = 'RESOLUE';
    case REJETEE = 'REJETEE';
    case CLOTUREE = 'CLOTUREE';

    public function label(): string
    {
        return match ($this) {
            self::NOUVELLE => 'Nouvelle',
            self::ASSIGNEE => 'Affectée',
            self::EN_COURS => 'En cours de traitement',
            self::EN_ATTENTE_JURIDICTION => 'En attente de la juridiction',
            self::RESOLUE => 'Résolue',
            self::REJETEE => 'Rejetée',
            self::CLOTUREE => 'Clôturée',
        };
    }

    public function couleur(): string
    {
        return match ($this) {
            self::NOUVELLE => 'info',
            self::ASSIGNEE => 'purple',
            self::EN_COURS => 'warning',
            self::EN_ATTENTE_JURIDICTION => 'neutral',
            self::RESOLUE => 'success',
            self::REJETEE => 'danger',
            self::CLOTUREE => 'dark',
        };
    }

    /**
     * Le dossier est encore actif (non finalisé).
     */
    public function estOuvert(): bool
    {
        return ! $this->estFinalise();
    }

    public function estFinalise(): bool
    {
        return in_array($this, [self::RESOLUE, self::REJETEE, self::CLOTUREE], true);
    }

    /**
     * Statuts sélectionnables manuellement par le support.
     *
     * @return array<StatutTicket>
     */
    public static function modifiablesManuellement(): array
    {
        return [
            self::ASSIGNEE,
            self::EN_COURS,
            self::EN_ATTENTE_JURIDICTION,
            self::RESOLUE,
            self::REJETEE,
            self::CLOTUREE,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function valeursOuvertes(): array
    {
        return array_values(array_map(
            static fn (self $statut): string => $statut->value,
            array_filter(self::cases(), static fn (self $statut): bool => $statut->estOuvert()),
        ));
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $statut) {
            $options[$statut->value] = $statut->label();
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function optionsModifiables(): array
    {
        $options = [];

        foreach (self::modifiablesManuellement() as $statut) {
            $options[$statut->value] = $statut->label();
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public static function valeurs(): array
    {
        return array_map(static fn (self $statut): string => $statut->value, self::cases());
    }
}
