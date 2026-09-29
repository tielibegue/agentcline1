<?php

namespace App\Models;

use App\Enums\ActionTicket;
use App\Enums\PrioriteTicket;
use App\Enums\StatutTicket;
use App\Enums\TypeTicket;
use App\Policies\TicketPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

#[UsePolicy(TicketPolicy::class)]
#[Fillable([
    'type',
    'priorite',
    'titre',
    'description',
    'application',
    'module_fonctionnel',
    'version_application',
    'environnement',
    'date_incident',
    'reproductible',
])]
class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Applications suivies par le support.
     *
     * @var array<string, string>
     */
    public const APPLICATIONS = [
        'just@' => 'just@ (poste de travail)',
        'just@ AJ' => 'just@ — module Agent Judiciaire',
        'appli1' => 'appli1 (portail web)',
        'appli2' => 'appli2 (API contentieux)',
        'appli3' => 'appli3 (API non-contentieux)',
        'appli4' => "appli4 (API d'authentification)",
        'Autre' => 'Autre / indéterminé',
    ];

    protected function casts(): array
    {
        return [
            'type' => TypeTicket::class,
            'priorite' => PrioriteTicket::class,
            'statut' => StatutTicket::class,
            'date_incident' => 'date',
            'reproductible' => 'boolean',
            'affecte_le' => 'datetime',
            'resolu_le' => 'datetime',
            'cloture_le' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $ticket): void {
            if (empty($ticket->reference)) {
                $ticket->reference = self::genererReference();
            }

            if (empty($ticket->statut)) {
                $ticket->statut = StatutTicket::NOUVELLE;
            }
        });
    }

    /**
     * Génère une référence unique du type SUP-2026-00001.
     */
    public static function genererReference(?int $annee = null): string
    {
        $annee ??= (int) now()->year;
        $prefixe = sprintf('SUP-%d-', $annee);

        $derniere = static::withTrashed()
            ->where('reference', 'like', $prefixe.'%')
            ->orderByDesc('reference')
            ->value('reference');

        $sequence = $derniere
            ? ((int) substr((string) $derniere, strlen($prefixe))) + 1
            : 1;

        return $prefixe.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }

    // -----------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------

    /**
     * @return BelongsTo<Juridiction, $this>
     */
    public function juridiction(): BelongsTo
    {
        return $this->belongsTo(Juridiction::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function declarant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'declarant_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assigneA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigne_a_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function resoluPar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolu_par_id');
    }

    /**
     * @return HasMany<TicketCommentaire, $this>
     */
    public function commentaires(): HasMany
    {
        return $this->hasMany(TicketCommentaire::class)->orderBy('created_at');
    }

    /**
     * @return HasMany<TicketCommentaire, $this>
     */
    public function commentairesVisibles(): HasMany
    {
        return $this->commentaires()->where('interne', false);
    }

    /**
     * @return HasMany<TicketPieceJointe, $this>
     */
    public function piecesJointes(): HasMany
    {
        return $this->hasMany(TicketPieceJointe::class)->orderByDesc('created_at');
    }

    /**
     * @return HasMany<TicketHistorique, $this>
     */
    public function historiques(): HasMany
    {
        return $this->hasMany(TicketHistorique::class)->orderByDesc('created_at');
    }

    // -----------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------

    #[Scope]
    protected function ouverts(Builder $query): void
    {
        $query->whereIn('statut', StatutTicket::valeursOuvertes());
    }

    #[Scope]
    protected function finalises(Builder $query): void
    {
        $query->whereNotIn('statut', StatutTicket::valeursOuvertes());
    }

    #[Scope]
    protected function enRetard(Builder $query): void
    {
        $delais = [
            PrioriteTicket::CRITIQUE->value => PrioriteTicket::CRITIQUE->delaiResolution(),
            PrioriteTicket::HAUTE->value => PrioriteTicket::HAUTE->delaiResolution(),
            PrioriteTicket::MOYENNE->value => PrioriteTicket::MOYENNE->delaiResolution(),
            PrioriteTicket::BASSE->value => PrioriteTicket::BASSE->delaiResolution(),
        ];

        $query->whereIn('statut', StatutTicket::valeursOuvertes())
            ->whereNotNull('created_at')
            ->where(function (Builder $sousRequete) use ($delais): void {
                foreach ($delais as $priorite => $heures) {
                    $sousRequete->orWhere(function (Builder $branche) use ($priorite, $heures): void {
                        $branche->where('priorite', $priorite)
                            ->where('created_at', '<', now()->subHours($heures));
                    });
                }
            });
    }

    /**
     * Restreint la liste à ce que l'utilisateur a le droit de consulter.
     */
    #[Scope]
    protected function pourUtilisateur(Builder $query, User $utilisateur): void
    {
        if ($utilisateur->estInterne()) {
            return;
        }

        $query->where(function (Builder $sousRequete) use ($utilisateur): void {
            $sousRequete->where('declarant_id', $utilisateur->id);

            if ($utilisateur->juridiction_id) {
                $sousRequete->orWhere('juridiction_id', $utilisateur->juridiction_id);
            }
        });
    }

    /**
     * Applique les filtres de la liste des demandes.
     *
     * @param  array<string, mixed>  $filtres
     */
    #[Scope]
    protected function filtres(Builder $query, array $filtres): void
    {
        $query
            ->when($filtres['recherche'] ?? null, function (Builder $q, string $recherche): void {
                $q->where(function (Builder $sousRequete) use ($recherche): void {
                    $terme = '%'.$recherche.'%';

                    $sousRequete->where('reference', 'like', $terme)
                        ->orWhere('titre', 'like', $terme)
                        ->orWhere('description', 'like', $terme)
                        ->orWhere('module_fonctionnel', 'like', $terme)
                        ->orWhere('application', 'like', $terme);
                });
            })
            ->when($filtres['statut'] ?? null, fn (Builder $q, string $statut) => $q->where('statut', $statut))
            ->when($filtres['type'] ?? null, fn (Builder $q, string $type) => $q->where('type', $type))
            ->when($filtres['priorite'] ?? null, fn (Builder $q, string $priorite) => $q->where('priorite', $priorite))
            ->when($filtres['application'] ?? null, fn (Builder $q, string $application) => $q->where('application', $application))
            ->when($filtres['juridiction_id'] ?? null, fn (Builder $q, $id) => $q->where('juridiction_id', $id))
            ->when($filtres['assigne_a_id'] ?? null, fn (Builder $q, $id) => $q->where('assigne_a_id', $id))
            ->when($filtres['non_affectes'] ?? null, fn (Builder $q) => $q->whereNull('assigne_a_id'))
            ->when($filtres['ouverts_seulement'] ?? null, fn (Builder $q) => $q->ouverts());
    }

    // -----------------------------------------------------------------
    // Lecteurs métier
    // -----------------------------------------------------------------

    public function estOuvert(): bool
    {
        return $this->statut instanceof StatutTicket && $this->statut->estOuvert();
    }

    public function estFinalise(): bool
    {
        return $this->statut instanceof StatutTicket && $this->statut->estFinalise();
    }

    public function estAffecte(): bool
    {
        return $this->assigne_a_id !== null;
    }

    /**
     * Délai de résolution attendu, en heures.
     */
    public function delaiResolutionHeures(): int
    {
        $priorite = $this->priorite instanceof PrioriteTicket
            ? $this->priorite
            : PrioriteTicket::MOYENNE;

        return $priorite->delaiResolution();
    }

    public function dateEcheance(): ?Carbon
    {
        return $this->created_at?->copy()->addHours($this->delaiResolutionHeures());
    }

    /**
     * La demande a dépassé le délai de résolution attendu.
     */
    public function estEnRetard(): bool
    {
        if (! $this->created_at || ! $this->estOuvert()) {
            return false;
        }

        return $this->dateEcheance()->isPast();
    }

    public function heuresDepuisCreation(): int
    {
        return $this->created_at ? (int) $this->created_at->diffInHours(now()) : 0;
    }

    /**
     * Délai de traitement réel, en heures (résolution ou instant présent).
     */
    public function heuresDeTraitement(): int
    {
        if (! $this->created_at) {
            return 0;
        }

        return (int) $this->created_at->diffInHours($this->resolu_le ?? now());
    }

    public function visiblePar(User $utilisateur): bool
    {
        if ($utilisateur->estInterne()) {
            return true;
        }

        if ($this->declarant_id === $utilisateur->id) {
            return true;
        }

        return $utilisateur->juridiction_id !== null
            && $this->juridiction_id === $utilisateur->juridiction_id;
    }

    public function libelleApplication(): string
    {
        if (! $this->application) {
            return 'Non précisée';
        }

        return self::APPLICATIONS[$this->application] ?? $this->application;
    }

    // -----------------------------------------------------------------
    // Actions métier
    // -----------------------------------------------------------------

    public function affecterA(?User $agent, ?User $auteur = null, ?string $commentaire = null): void
    {
        $ancien = $this->assigneA?->name;

        $this->assigne_a_id = $agent?->id;
        $this->affecte_le = $agent ? now() : null;

        if ($agent && $this->statut === StatutTicket::NOUVELLE) {
            $this->statut = StatutTicket::ASSIGNEE;
        }

        $this->save();

        $this->journaliser(
            ActionTicket::AFFECTATION,
            $ancien,
            $agent?->name,
            $commentaire ?? ($agent ? 'Demande affectée à '.$agent->name.'.' : 'Affectation retirée.'),
            $auteur,
        );
    }

    public function changerStatut(StatutTicket $nouveauStatut, ?User $auteur = null, ?string $commentaire = null): void
    {
        $ancienStatut = $this->statut;

        $this->statut = $nouveauStatut;

        if ($nouveauStatut === StatutTicket::CLOTUREE) {
            $this->cloture_le = $this->cloture_le ?? now();
        } elseif ($nouveauStatut->estOuvert()) {
            $this->cloture_le = null;
        }

        $this->save();

        $this->journaliser(
            ActionTicket::CHANGEMENT_STATUT,
            $ancienStatut instanceof StatutTicket ? $ancienStatut->label() : null,
            $nouveauStatut->label(),
            $commentaire,
            $auteur,
        );
    }

    public function resoudre(User $auteur, string $resolution): void
    {
        $ancienStatut = $this->statut;

        $this->resolution = $resolution;
        $this->resolu_par_id = $auteur->id;
        $this->resolu_le = now();
        $this->statut = StatutTicket::RESOLUE;
        $this->save();

        $this->journaliser(
            ActionTicket::RESOLUTION,
            $ancienStatut instanceof StatutTicket ? $ancienStatut->label() : null,
            StatutTicket::RESOLUE->label(),
            $resolution,
            $auteur,
        );
    }

    public function rejeter(User $auteur, string $motif): void
    {
        $ancienStatut = $this->statut;

        $this->motif_rejet = $motif;
        $this->statut = StatutTicket::REJETEE;
        $this->cloture_le = now();
        $this->save();

        $this->journaliser(
            ActionTicket::REJET,
            $ancienStatut instanceof StatutTicket ? $ancienStatut->label() : null,
            StatutTicket::REJETEE->label(),
            $motif,
            $auteur,
        );
    }

    public function cloturer(User $auteur, ?string $commentaire = null): void
    {
        $ancienStatut = $this->statut;

        $this->statut = StatutTicket::CLOTUREE;
        $this->cloture_le = now();
        $this->save();

        $this->journaliser(
            ActionTicket::CLOTURE,
            $ancienStatut instanceof StatutTicket ? $ancienStatut->label() : null,
            StatutTicket::CLOTUREE->label(),
            $commentaire ?? 'Demande clôturée.',
            $auteur,
        );
    }

    public function journaliser(
        ActionTicket $action,
        ?string $ancienneValeur = null,
        ?string $nouvelleValeur = null,
        ?string $commentaire = null,
        ?User $auteur = null,
    ): TicketHistorique {
        return $this->historiques()->create([
            'user_id' => $auteur?->id,
            'action' => $action,
            'ancienne_valeur' => $ancienneValeur,
            'nouvelle_valeur' => $nouvelleValeur,
            'commentaire' => $commentaire,
        ]);
    }

    /**
     * Enregistre une pièce jointe téléversée sur ce ticket.
     */
    public function ajouterPieceJointe(UploadedFile $fichier, ?User $auteur = null): TicketPieceJointe
    {
        $chemin = $fichier->store('pieces-jointes/'.$this->id, 'local');

        $piece = $this->piecesJointes()->create([
            'user_id' => $auteur?->id,
            'nom_original' => $fichier->getClientOriginalName(),
            'chemin' => $chemin,
            'type_mime' => $fichier->getClientMimeType(),
            'taille' => $fichier->getSize(),
        ]);

        $this->journaliser(
            ActionTicket::PIECE_AJOUTEE,
            null,
            $piece->nom_original,
            'Fichier téléversé : '.$piece->nom_original.' ('.$piece->tailleLisible().').',
            $auteur,
        );

        return $piece;
    }
}
