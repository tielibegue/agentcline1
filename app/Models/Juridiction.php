<?php

namespace App\Models;

use App\Enums\StatutTicket;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'code',
    'libelle',
    'type',
    'ville',
    'commune',
    'telephone',
    'email',
    'responsable',
    'actif',
])]
class Juridiction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Types de juridiction reconnus.
     *
     * @var array<string, string>
     */
    public const TYPES = [
        'COUR_APPEL' => "Cour d'appel",
        'TRIBUNAL_COMMERCE' => 'Tribunal de commerce',
        'TRIBUNAL_PREMIERE_INSTANCE' => 'Tribunal de première instance',
        'TRIBUNAL_TRAVAIL' => 'Tribunal du travail',
        'COUR_ASSISES' => "Cour d'assises",
        'PARQUET' => 'Parquet',
        'AUTRE' => 'Autre structure',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function utilisateurs(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function libelleType(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function nomComplet(): string
    {
        return $this->libelle.($this->ville ? ' ('.$this->ville.')' : '');
    }

    public function ticketsOuverts(): HasMany
    {
        return $this->tickets()->whereIn('statut', StatutTicket::valeursOuvertes());
    }
}
