<?php

namespace App\Enums;

/**
 * Rôles disponibles dans l'application de support Agent-Justice.
 */
enum RoleUtilisateur: string
{
    case ADMIN = 'ADMIN';
    case SUPPORT = 'SUPPORT';
    case JURIDICTION = 'JURIDICTION';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrateur',
            self::SUPPORT => 'Agent de support',
            self::JURIDICTION => 'Agent de juridiction',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADMIN => "Administre les juridictions, les comptes et l'ensemble des demandes.",
            self::SUPPORT => 'Traite, affecte et résout les plaintes et bugs déclarés.',
            self::JURIDICTION => 'Déclare les plaintes et anomalies constatées sur le terrain.',
        };
    }

    /**
     * Un utilisateur interne (admin ou support) traite les demandes.
     */
    public function estInterne(): bool
    {
        return $this !== self::JURIDICTION;
    }

    public function estAdministrateur(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * @return array<int, string>
     */
    public static function valeurs(): array
    {
        return array_map(static fn (self $role): string => $role->value, self::cases());
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $role) {
            $options[$role->value] = $role->label();
        }

        return $options;
    }
}
