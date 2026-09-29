<?php

namespace App\Models;

use App\Enums\RoleUtilisateur;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'role',
    'juridiction_id',
    'fonction',
    'telephone',
    'actif',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => RoleUtilisateur::class,
            'actif' => 'boolean',
            'dernier_acces_le' => 'datetime',
        ];
    }

    /**
     * Juridiction de rattachement (comptes de juridiction).
     *
     * @return BelongsTo<Juridiction, $this>
     */
    public function juridiction(): BelongsTo
    {
        return $this->belongsTo(Juridiction::class);
    }

    /**
     * Demandes déclarées par cet utilisateur.
     *
     * @return HasMany<Ticket, $this>
     */
    public function ticketsDeclares(): HasMany
    {
        return $this->hasMany(Ticket::class, 'declarant_id');
    }

    /**
     * Demandes affectées à cet utilisateur (support).
     *
     * @return HasMany<Ticket, $this>
     */
    public function ticketsAssignes(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigne_a_id');
    }

    /**
     * @return HasMany<TicketCommentaire, $this>
     */
    public function commentaires(): HasMany
    {
        return $this->hasMany(TicketCommentaire::class);
    }

    public function role(): RoleUtilisateur
    {
        return $this->role instanceof RoleUtilisateur
            ? $this->role
            : RoleUtilisateur::from((string) $this->role);
    }

    public function estInterne(): bool
    {
        return $this->role()->estInterne();
    }

    public function estAdministrateur(): bool
    {
        return $this->role()->estAdministrateur();
    }

    public function estSupport(): bool
    {
        return $this->role() === RoleUtilisateur::SUPPORT;
    }

    public function estJuridiction(): bool
    {
        return $this->role() === RoleUtilisateur::JURIDICTION;
    }

    public function libelleRole(): string
    {
        return $this->role()->label();
    }

    public function initiales(): string
    {
        $mots = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $initiales = '';

        foreach (array_slice($mots, 0, 2) as $mot) {
            $initiales .= mb_strtoupper(mb_substr($mot, 0, 1));
        }

        return $initiales !== '' ? $initiales : '?';
    }
}
