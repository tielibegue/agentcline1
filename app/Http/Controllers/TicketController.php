<?php

namespace App\Http\Controllers;

use App\Enums\ActionTicket;
use App\Enums\PrioriteTicket;
use App\Enums\StatutTicket;
use App\Enums\TypeTicket;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Juridiction;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    /**
     * Liste des demandes avec filtres, tri et pagination.
     */
    public function index(Request $requete)
    {
        $this->authorize('viewAny', Ticket::class);

        $tri = $requete->string('tri', 'recent')->toString();
        $sens = $requete->string('sens', 'desc')->toString() === 'asc' ? 'asc' : 'desc';

        $colonnesTri = [
            'recent' => 'created_at',
            'echeance' => 'created_at',
            'priorite' => 'priorite',
            'statut' => 'statut',
            'titre' => 'titre',
        ];

        $tickets = Ticket::query()
            ->with(['juridiction', 'declarant', 'assigneA'])
            ->pourUtilisateur($requete->user())
            ->filtres($requete->only([
                'recherche', 'statut', 'type', 'priorite', 'application',
                'juridiction_id', 'assigne_a_id', 'non_affectes', 'ouverts_seulement',
            ]))
            ->when(
                $tri === 'echeance',
                fn ($q) => $q->orderByRaw(
                    "datetime(created_at, '+' || CASE priorite WHEN 'CRITIQUE' THEN 8 WHEN 'HAUTE' THEN 24 WHEN 'MOYENNE' THEN 72 ELSE 120 END || ' hours') {$sens}"
                ),
                fn ($q) => $q->orderBy($colonnesTri[$tri] ?? 'created_at', $sens)
            )
            ->paginate(15)
            ->withQueryString();

        return view('tickets.index', [
            'tickets' => $tickets,
            'filtres' => $requete->only([
                'recherche', 'statut', 'type', 'priorite', 'application',
                'juridiction_id', 'assigne_a_id', 'non_affectes', 'ouverts_seulement',
            ]),
            'tri' => $tri,
            'sens' => $sens,
            'statuts' => StatutTicket::options(),
            'types' => TypeTicket::options(),
            'priorites' => PrioriteTicket::options(),
            'applications' => Ticket::APPLICATIONS,
            'juridictions' => $requete->user()->estInterne()
                ? Juridiction::query()->orderBy('libelle')->get(['id', 'libelle', 'ville'])
                : collect(),
            'agents' => $requete->user()->estInterne()
                ? User::query()->where('role', 'SUPPORT')->where('actif', true)->orderBy('name')->get(['id', 'name'])
                : collect(),
        ]);
    }

    /**
     * Formulaire de déclaration d'une nouvelle demande.
     */
    public function create(Request $requete)
    {
        $this->authorize('create', Ticket::class);

        return view('tickets.create', [
            'types' => TypeTicket::options(),
            'priorites' => PrioriteTicket::options(),
            'applications' => Ticket::APPLICATIONS,
            'juridictions' => $requete->user()->estInterne()
                ? Juridiction::query()->where('actif', true)->orderBy('libelle')->get()
                : collect(),
        ]);
    }

    /**
     * Enregistre une demande déclarée par une juridiction.
     */
    public function store(StoreTicketRequest $requete): RedirectResponse
    {
        $this->authorize('create', Ticket::class);

        $donnees = $requete->validated();
        $utilisateur = $requete->user();

        $ticket = new Ticket($donnees);

        // Une juridiction déclare toujours pour sa propre structure.
        $ticket->juridiction_id = $utilisateur->estInterne()
            ? ($donnees['juridiction_id'] ?? null)
            : $utilisateur->juridiction_id;

        $ticket->declarant_id = $utilisateur->id;
        $ticket->save();

        $ticket->journaliser(
            ActionTicket::CREATION,
            null,
            StatutTicket::NOUVELLE->label(),
            'Demande déclarée par '.$utilisateur->name.'.',
            $utilisateur,
        );

        foreach ($requete->file('pieces_jointes', []) as $fichier) {
            $ticket->ajouterPieceJointe($fichier, $utilisateur);
        }

        return redirect()
            ->route('demandes.show', $ticket)
            ->with('succes', 'Demande '.$ticket->reference.' enregistrée. Le support va la prendre en charge.');
    }

    /**
     * Fiche complète d'une demande : détails, échanges, pièces et historique.
     */
    public function show(Request $requete, Ticket $ticket)
    {
        $this->authorize('view', $ticket);
        $utilisateur = $requete->user();

        $ticket->load(['juridiction', 'declarant', 'assigneA', 'resoluPar']);

        $commentaires = $ticket->commentaires()
            ->with('auteur')
            ->when(! $utilisateur->estInterne(), fn ($q) => $q->where('interne', false))
            ->orderBy('created_at')
            ->get();

        $pieces = $ticket->piecesJointes()->with('auteur')->orderByDesc('created_at')->get();
        $historiques = $ticket->historiques()->with('auteur')->orderByDesc('created_at')->get();

        $agents = $utilisateur->estInterne()
            ? User::query()->where('role', 'SUPPORT')->where('actif', true)->orderBy('name')->get()
            : collect();

        return view('tickets.show', [
            'ticket' => $ticket,
            'commentaires' => $commentaires,
            'pieces' => $pieces,
            'historiques' => $historiques,
            'agents' => $agents,
            'statutsModifiables' => StatutTicket::optionsModifiables(),
        ]);
    }

    /**
     * Formulaire de modification du contenu (support uniquement).
     */
    public function edit(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        return view('tickets.edit', [
            'ticket' => $ticket,
            'types' => TypeTicket::options(),
            'priorites' => PrioriteTicket::options(),
            'applications' => Ticket::APPLICATIONS,
        ]);
    }

    public function update(UpdateTicketRequest $requete, Ticket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $ticket->fill($requete->validated());
        $ticket->save();

        $ticket->journaliser(
            ActionTicket::MODIFICATION,
            null,
            null,
            'Contenu de la demande modifié par '.$requete->user()->name.'.',
            $requete->user(),
        );

        return redirect()
            ->route('demandes.show', $ticket)
            ->with('succes', 'Demande '.$ticket->reference.' mise à jour.');
    }

    public function destroy(Request $requete, Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        $reference = $ticket->reference;
        $ticket->delete();

        return redirect()
            ->route('demandes.index')
            ->with('succes', 'Demande '.$reference.' supprimée (corbeille).');
    }

    // -----------------------------------------------------------------
    // Traitement par le support : affectation, statut, résolution.
    // -----------------------------------------------------------------

    public function affecter(Request $requete, Ticket $ticket): RedirectResponse
    {
        $this->authorize('affecter', $ticket);

        $donnees = $requete->validate([
            'assigne_a_id' => ['nullable', 'integer', 'exists:users,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [], ['assigne_a_id' => 'agent', 'note' => 'note']);

        $agent = $donnees['assigne_a_id'] ? User::query()->findOrFail($donnees['assigne_a_id']) : null;

        abort_if($agent && ! $agent->estInterne(), 422, "L'agent désigné doit appartenir au support.");

        $ticket->affecterA($agent, $requete->user(), $donnees['note'] ?? null);

        return redirect()
            ->route('demandes.show', $ticket)
            ->with('succes', $agent ? 'Demande affectée à '.$agent->name.'.' : 'Affectation retirée.');
    }

    public function changerStatut(Request $requete, Ticket $ticket): RedirectResponse
    {
        $this->authorize('changerStatut', $ticket);

        $donnees = $requete->validate([
            'statut' => ['required', 'string', Rule::in(array_keys(StatutTicket::optionsModifiables()))],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [], ['statut' => 'statut', 'note' => 'note']);

        $ticket->changerStatut(StatutTicket::from($donnees['statut']), $requete->user(), $donnees['note'] ?? null);

        return redirect()
            ->route('demandes.show', $ticket)
            ->with('succes', 'Statut passé à « '.StatutTicket::from($donnees['statut'])->label().' ».');
    }

    /**
     * Le support consigne la résolution de la demande.
     */
    public function resoudre(Request $requete, Ticket $ticket): RedirectResponse
    {
        $this->authorize('resoudre', $ticket);

        $donnees = $requete->validate([
            'resolution' => ['required', 'string', 'min:10', 'max:5000'],
        ], [], ['resolution' => 'description de la résolution']);

        $ticket->resoudre($requete->user(), (string) $donnees['resolution']);

        return redirect()
            ->route('demandes.show', $ticket)
            ->with('succes', 'Demande '.$ticket->reference.' marquée comme résolue.');
    }

    public function rejeter(Request $requete, Ticket $ticket): RedirectResponse
    {
        $this->authorize('rejeter', $ticket);

        $donnees = $requete->validate([
            'motif_rejet' => ['required', 'string', 'min:10', 'max:2000'],
        ], [], ['motif_rejet' => 'motif du rejet']);

        $ticket->rejeter($requete->user(), (string) $donnees['motif_rejet']);

        return redirect()
            ->route('demandes.show', $ticket)
            ->with('succes', 'Demande '.$ticket->reference.' rejetée (motif consigné).');
    }

    /**
     * Clôture par le support ou confirmation par la juridiction.
     */
    public function cloturer(Request $requete, Ticket $ticket): RedirectResponse
    {
        $this->authorize('cloturer', $ticket);

        $donnees = $requete->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ], [], ['note' => 'note']);

        $ticket->cloturer($requete->user(), $donnees['note'] ?? null);

        return redirect()
            ->route('demandes.show', $ticket)
            ->with('succes', 'Demande '.$ticket->reference.' clôturée.');
    }

    // -----------------------------------------------------------------
    // Export CSV de la liste filtrée.
    // -----------------------------------------------------------------

    public function export(Request $requete): StreamedResponse
    {
        $this->authorize('viewAny', Ticket::class);

        $nomFichier = 'demandes-support-'.now()->format('Ymd-His').'.csv';

        $tickets = Ticket::query()
            ->with(['juridiction', 'declarant', 'assigneA'])
            ->pourUtilisateur($requete->user())
            ->filtres($requete->only(['recherche', 'statut', 'type', 'priorite', 'application', 'juridiction_id', 'assigne_a_id', 'ouverts_seulement']))
            ->orderByDesc('created_at')
            ->limit(5000)
            ->get();

        $colonnes = [
            'Référence', 'Date de déclaration', 'Type', 'Priorité', 'Statut',
            'Objet', 'Application', 'Module', "Date d'incident",
            'Juridiction', 'Déclarant', 'Affecté à',
            'Résolu le', 'Clôturé le', 'Heures de traitement',
        ];

        return response()->streamDownload(function () use ($tickets, $colonnes): void {
            $flux = fopen('php://output', 'w');
            fwrite($flux, "\xEF\xBB\xBF");
            fputcsv($flux, $colonnes, ';');

            foreach ($tickets as $ticket) {
                fputcsv($flux, [
                    $ticket->reference,
                    $ticket->created_at?->format('d/m/Y H:i'),
                    $ticket->type instanceof TypeTicket ? $ticket->type->label() : $ticket->type,
                    $ticket->priorite instanceof PrioriteTicket ? $ticket->priorite->label() : $ticket->priorite,
                    $ticket->statut instanceof StatutTicket ? $ticket->statut->label() : $ticket->statut,
                    $ticket->titre,
                    $ticket->libelleApplication(),
                    $ticket->module_fonctionnel,
                    $ticket->date_incident?->format('d/m/Y'),
                    $ticket->juridiction?->nomComplet(),
                    $ticket->declarant?->name,
                    $ticket->assigneA?->name,
                    $ticket->resolu_le?->format('d/m/Y H:i'),
                    $ticket->cloture_le?->format('d/m/Y H:i'),
                    $ticket->heuresDeTraitement(),
                ], ';');
            }

            fclose($flux);
        }, $nomFichier, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
