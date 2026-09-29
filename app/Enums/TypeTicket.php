<?php

namespace App\Enums;

/**
 * Nature de la demande déposée par une juridiction.
 */
enum TypeTicket: string
{
    case BUG = 'BUG';
    case PLAINTE = 'PLAINTE';
    case DEMANDE = 'DEMANDE';
    case INCIDENT = 'INCIDENT';

    public function label(): string
    {
        return match ($this) {
            self::BUG => 'Bug / anomalie applicative',
            self::PLAINTE => 'Plainte utilisateur',
            self::DEMANDE => "Demande d'assistance",
            self::INCIDENT => 'Incident bloquant',
        };
    }

    public function couleur(): string
    {
        return match ($this) {
            self::BUG => 'danger',
            self::PLAINTE => 'warning',
            self::DEMANDE => 'info',
            self::INCIDENT => 'purple',
        };
    }

    /**
     * Délai indicatif de prise en charge, en heures.
     */
    public function delaiPriseEnCharge(): int
    {
        return match ($this) {
            self::INCIDENT => 4,
            self::BUG => 24,
            self::PLAINTE => 48,
            self::DEMANDE => 72,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $type) {
            $options[$type->value] = $type->label();
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public static function valeurs(): array
    {
        return array_map(static fn (self $type): string => $type->value, self::cases());
    }
}
