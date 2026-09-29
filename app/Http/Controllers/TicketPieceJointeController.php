<?php

namespace App\Http\Controllers;

use App\Enums\ActionTicket;
use App\Models\Ticket;
use App\Models\TicketPieceJointe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketPieceJointeController extends Controller
{
    /**
     * Extensions autorisées pour les pièces jointes.
     *
     * @var array<int, string>
     */
    public const EXTENSIONS_AUTORISEES = [
        'jpg', 'jpeg', 'png', 'gif', 'pdf',
        'doc', 'docx', 'xls', 'xlsx', 'csv',
        'txt', 'log', 'zip',
    ];

    public function store(Request $requete, Ticket $ticket): RedirectResponse
    {
        $this->authorize('ajouterPieceJointe', $ticket);

        $requete->validate([
            'pieces_jointes' => ['required', 'array', 'max:5'],
            'pieces_jointes.*' => ['file', 'max:10240', 'mimes:'.implode(',', self::EXTENSIONS_AUTORISEES)],
        ], [
            'pieces_jointes.required' => 'Sélectionnez au moins un fichier à joindre.',
            'pieces_jointes.max' => 'Vous ne pouvez joindre que 5 fichiers par envoi.',
            'pieces_jointes.*.max' => 'Chaque pièce jointe ne doit pas dépasser 10 Mo.',
        ]);

        foreach ($requete->file('pieces_jointes', []) as $fichier) {
            $chemin = $fichier->store('pieces-jointes/'.$ticket->id, 'local');

            $piece = $ticket->piecesJointes()->create([
                'user_id' => $requete->user()->id,
                'nom_original' => $fichier->getClientOriginalName(),
                'chemin' => $chemin,
                'type_mime' => $fichier->getClientMimeType(),
                'taille' => $fichier->getSize(),
            ]);

            $ticket->journaliser(
                ActionTicket::PIECE_AJOUTEE,
                null,
                $piece->nom_original,
                'Fichier téléversé : '.$piece->nom_original.' ('.$piece->tailleLisible().').',
                $requete->user(),
            );
        }

        return back()->with('succes', 'Pièce(s) jointe(s) ajoutée(s) à la demande.');
    }

    public function download(Request $requete, Ticket $ticket, TicketPieceJointe $pieceJointe): StreamedResponse
    {
        $this->authorize('view', $ticket);
        abort_unless($pieceJointe->ticket_id === $ticket->id, 404);
        abort_unless(Storage::disk('local')->exists($pieceJointe->chemin), 404);

        return Storage::disk('local')->download($pieceJointe->chemin, $pieceJointe->nom_original);
    }

    public function destroy(Request $requete, Ticket $ticket, TicketPieceJointe $pieceJointe): RedirectResponse
    {
        $this->authorize('supprimerPieceJointe', $ticket);
        abort_unless($pieceJointe->ticket_id === $ticket->id, 404);

        $nom = $pieceJointe->nom_original;

        Storage::disk('local')->delete($pieceJointe->chemin);
        $pieceJointe->delete();

        $ticket->journaliser(
            ActionTicket::PIECE_SUPPRIMEE,
            $nom,
            null,
            'Pièce jointe supprimée : '.$nom.'.',
            $requete->user(),
        );

        return back()->with('succes', 'Pièce jointe supprimée.');
    }
}
