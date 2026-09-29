<?php

namespace App\Enums;

/**
 * Niveau d'urgence d'une demande.
 */
enum PrioriteTicket: string
{
    case BASSE = 'BASSE';
    case MOYENNE = 'MOYENNE';
    case HAUTE = 'HAUTE';
    case CRITIQUE = 'CRITIQUE';

    public function label(): string
    {
        return match ($this) {
            self::BASSE => 'Basse',
            self::MOYENNE => 'Moyenne',
            self::HAUTE => 'Haute',
            self::CRITIQUE => 'Critique',
        };
    }

    public function couleur(): string
    {
        return match ($this) {
            self::BASSE => 'neutral',
            self::MOYENNE => 'info',
            self::HAUTE => 'warning',
            self::CRITIQUE => 'danger',
        };
    }

    /**
     * Ordre de tri décroissant d'urgence (4 = le plus urgent).
     */
    public function ordre(): int
    {
        return match ($this) {
            self::BASSE => 1,
            self::MOYENNE => 2,
            self::HAUTE => 3,
            self::CRITIQUE => 4,
        };
    }

    /**
     * Délai de résolution indicatif, en heures.
     */
    public function delaiResolution(): int
    {
        return match ($this) {
            self::BASSE => 120,
            self::MOYENNE => 72,
            self::HAUTE => 24,
            self::CRITIQUE => 8,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $priorite) {
            $options[$priorite->value] = $priorite->label();
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public static function valeurs(): array
    {
        return array_map(static fn (self $priorite): string => $priorite->value, self::cases());
    }
}
