<?php

namespace App\Http\Controllers;

use App\Enums\StatutTicket;
use App\Models\Juridiction;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $requete): View
    {
        $utilisateur = $requete->user();

        $base = Ticket::query()->pourUtilisateur($utilisateur);

        $total = (clone $base)->count();
        $ouverts = (clone $base)->ouverts()->count();
        $enRetard = (clone $base)->enRetard()->count();
        $nonAffectes = (clone $base)->ouverts()->whereNull('assigne_a_id')->count();
        $resolus = (clone $base)->where('statut', StatutTicket::RESOLUE)->count();

        $tauxResolution = $total > 0
            ? round(($resolus / $total) * 100, 1)
            : 0.0;

        $parStatut = $this->repartitionPar(clone $base, 'statut');
        $parType = $this->repartitionPar(clone $base, 'type');
        $parPriorite = $this->repartitionPar(clone $base, 'priorite');

        $delaiMoyenHeures = $this->delaiMoyenResolution(clone $base);

        $derniersTickets = (clone $base)
            ->with(['juridiction', 'declarant', 'assigneA'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $ticketsAssignes = $utilisateur->estInterne()
            ? Ticket::query()
                ->ouverts()
                ->where('assigne_a_id', $utilisateur->id)
                ->with('juridiction')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
            : collect();

        $topJuridictions = $utilisateur->estInterne()
            ? Juridiction::query()
                ->withCount(['tickets', 'ticketsOuverts'])
                ->orderByDesc('tickets_count')
                ->limit(6)
                ->get()
            : collect();

        return view('dashboard', [
            'total' => $total,
            'ouverts' => $ouverts,
            'enRetard' => $enRetard,
            'nonAffectes' => $nonAffectes,
            'resolus' => $resolus,
            'tauxResolution' => $tauxResolution,
            'delaiMoyenHeures' => $delaiMoyenHeures,
            'parStatut' => $parStatut,
            'parType' => $parType,
            'parPriorite' => $parPriorite,
            'derniersTickets' => $derniersTickets,
            'ticketsAssignes' => $ticketsAssignes,
            'topJuridictions' => $topJuridictions,
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function repartitionPar(Builder $requete, string $colonne): array
    {
        $resultat = [];

        $lignes = $requete->selectRaw("{$colonne}, COUNT(*) as total")->groupBy($colonne)->get();

        foreach ($lignes as $ligne) {
            $valeur = $ligne->getAttribute($colonne);
            $cle = $valeur instanceof \BackedEnum ? $valeur->value : (string) $valeur;

            if ($cle !== '') {
                $resultat[$cle] = (int) $ligne->getAttribute('total');
            }
        }

        return $resultat;
    }

    /**
     * Délai moyen de résolution (heures) sur le mois en cours.
     */
    private function delaiMoyenResolution(Builder $requete): ?float
    {
        $tickets = $requete
            ->where('statut', StatutTicket::RESOLUE)
            ->where('resolu_le', '>=', now()->startOfMonth())
            ->get(['created_at', 'resolu_le']);

        if ($tickets->isEmpty()) {
            return null;
        }

        $total = $tickets->sum(static fn (Ticket $ticket): int => $ticket->heuresDeTraitement());

        return round($total / $tickets->count(), 1);
    }
}
