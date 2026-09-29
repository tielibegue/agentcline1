<?php

namespace App\Http\Controllers;

use App\Enums\ActionTicket;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketCommentaireController extends Controller
{
    /**
     * @return array<string, string>
     */
    private function attributs(): array
    {
        return [
            'message' => 'message',
            'interne' => 'note interne',
        ];
    }

    public function store(Request $requete, Ticket $ticket): RedirectResponse
    {
        $this->authorize('commenter', $ticket);

        $donnees = $requete->validate([
            'message' => ['required', 'string', 'min:2', 'max:4000'],
            'interne' => ['nullable', 'boolean'],
        ], [], $this->attributs());

        // Seul le support peut rédiger des notes internes.
        $interne = (bool) ($donnees['interne'] ?? false) && $requete->user()->estInterne();

        $ticket->commentaires()->create([
            'user_id' => $requete->user()->id,
            'message' => $donnees['message'],
            'interne' => $interne,
        ]);

        $ticket->journaliser(
            ActionTicket::COMMENTAIRE,
            null,
            $interne ? 'Note interne' : 'Commentaire',
            Str::limit((string) $donnees['message'], 200),
            $requete->user(),
        );

        return back()->with('succes', $interne ? 'Note interne enregistrée.' : 'Commentaire ajouté à la demande.');
    }
}
