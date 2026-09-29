<?php

namespace Tests\Feature;

use App\Enums\StatutTicket;
use App\Models\Juridiction;
use App\Models\Ticket;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DeclarationDemandeTest extends SupportTestCase
{
    public function test_une_juridiction_peut_declarer_une_demande(): void
    {
        $this->actingAs($this->juridictionnaire)
            ->post('/demandes', [
                'type' => 'BUG',
                'priorite' => 'HAUTE',
                'titre' => 'Impression du plumitif impossible',
                'description' => "L'impression du plumitif d'audience échoue sur tous les postes du greffe depuis la mise à jour.",
                'application' => 'E-TribCom AJ',
                'module_fonctionnel' => 'ui/aj/co/plumitif',
                'date_incident' => now()->format('Y-m-d'),
            ])
            ->assertRedirect();

        $ticket = Ticket::query()->first();

        $this->assertNotNull($ticket);
        $this->assertMatchesRegularExpression('/^SUP-\d{4}-\d{5}$/', $ticket->reference);
        $this->assertSame(StatutTicket::NOUVELLE, $ticket->statut);
        $this->assertSame($this->juridictionnaire->id, $ticket->declarant_id);
        $this->assertSame($this->juridiction->id, $ticket->juridiction_id);
    }

    public function test_la_juridiction_est_forcee_a_sa_propre_structure(): void
    {
        $autre = Juridiction::factory()->create(['code' => 'TCA-BKE']);

        $this->actingAs($this->juridictionnaire)
            ->post('/demandes', [
                'type' => 'PLAINTE',
                'priorite' => 'BASSE',
                'titre' => 'Lenteur à la consultation des dossiers',
                'description' => 'La consultation des dossiers est extrêmement lente aux heures de pointe, bloquant le travail.',
                'juridiction_id' => $autre->id,
            ]);

        $ticket = Ticket::query()->sole();

        $this->assertSame($this->juridiction->id, $ticket->juridiction_id);
    }

    public function test_le_support_peut_declarer_pour_une_juridiction(): void
    {
        $this->actingAs($this->support)
            ->post('/demandes', [
                'type' => 'DEMANDE',
                'priorite' => 'MOYENNE',
                'titre' => 'Ajout d’une chambre au référentiel',
                'description' => 'Demande téléphonique du greffe : ajouter la chambre commerciale numéro 4 au référentiel.',
                'juridiction_id' => $this->juridiction->id,
            ])
            ->assertRedirect();

        $ticket = Ticket::query()->sole();

        $this->assertSame($this->juridiction->id, $ticket->juridiction_id);
        $this->assertSame($this->support->id, $ticket->declarant_id);
    }

    public function test_les_donnees_invalides_sont_rejetees(): void
    {
        $this->actingAs($this->juridictionnaire)
            ->post('/demandes', [
                'type' => 'INCONNU',
                'priorite' => 'MOYENNE',
                'titre' => 'Bug',
                'description' => 'Trop court.',
            ])
            ->assertSessionHasErrors(['type', 'titre', 'description']);

        $this->assertSame(0, Ticket::query()->count());
    }

    public function test_declaration_avec_pieces_jointes(): void
    {
        Storage::fake('local');

        $this->actingAs($this->juridictionnaire)
            ->post('/demandes', [
                'type' => 'BUG',
                'priorite' => 'HAUTE',
                'titre' => 'Capture et journal joints au signalement',
                'description' => "Constat d'anomalie à l'ouverture du module : capture d'écran et journal joints à la demande.",
                'pieces_jointes' => [
                    UploadedFile::fake()->image('capture.png'),
                    UploadedFile::fake()->create('journal.log', 50, 'text/plain'),
                ],
            ])
            ->assertRedirect();

        $ticket = Ticket::query()->sole();

        $this->assertSame(2, $ticket->piecesJointes()->count());
        $this->assertSame(1, $ticket->historiques()->where('action', 'CREATION')->count());
        $this->assertSame(2, $ticket->historiques()->where('action', 'PIECE_AJOUTEE')->count());
    }
}
