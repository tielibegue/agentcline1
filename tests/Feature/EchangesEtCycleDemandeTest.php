<?php

namespace Tests\Feature;

use App\Enums\ActionTicket;
use App\Enums\StatutTicket;
use App\Models\Ticket;

class EchangesEtCycleDemandeTest extends SupportTestCase
{
    public function test_une_juridiction_peut_commenter_sa_demande(): void
    {
        $ticket = $this->declarerTicket($this->juridictionnaire);

        $this->actingAs($this->juridictionnaire)
            ->post('/demandes/'.$ticket->id.'/commentaires', [
                'message' => "Précision : l'erreur se produit aussi sur le poste de l'adjoint.",
            ])
            ->assertRedirect();

        $this->assertSame(1, $ticket->commentaires()->count());
        $this->assertFalse((bool) $ticket->commentaires()->first()->interne);
    }

    public function test_seul_le_support_redige_des_notes_internes(): void
    {
        $ticket = $this->declarerTicket($this->juridictionnaire);

        $this->actingAs($this->juridictionnaire)
            ->post('/demandes/'.$ticket->id.'/commentaires', [
                'message' => 'Tentative de note interne par une juridiction.',
                'interne' => true,
            ]);

        $this->assertFalse((bool) $ticket->commentaires()->first()->interne);

        $this->actingAs($this->support)
            ->post('/demandes/'.$ticket->id.'/commentaires', [
                'message' => 'Vraie note interne du support.',
                'interne' => true,
            ]);

        $this->assertTrue((bool) $ticket->commentaires()->orderByDesc('id')->first()->interne);
    }

    public function test_une_juridiction_ne_voit_pas_les_notes_internes(): void
    {
        $ticket = $this->declarerTicket($this->juridictionnaire);

        $ticket->commentaires()->create([
            'user_id' => $this->support->id,
            'message' => 'Analyse interne réservée au support.',
            'interne' => true,
        ]);

        $reponse = $this->actingAs($this->juridictionnaire)->get('/demandes/'.$ticket->id);

        $reponse->assertOk();
        $reponse->assertDontSee('Analyse interne réservée au support.');
    }

    public function test_le_cycle_complet_laisse_une_trace_complete(): void
    {
        $ticket = $this->declarerTicket($this->juridictionnaire);

        $this->actingAs($this->support)->post('/demandes/'.$ticket->id.'/affecter', ['assigne_a_id' => $this->support->id]);
        $this->actingAs($this->support)->post('/demandes/'.$ticket->id.'/statut', ['statut' => StatutTicket::EN_COURS->value]);
        $this->actingAs($this->support)->post('/demandes/'.$ticket->id.'/resoudre', ['resolution' => 'Résolution complète et vérifiée.']);

        $this->actingAs($this->juridictionnaire)->post('/demandes/'.$ticket->id.'/cloturer');

        $actions = $ticket->historiques()->pluck('action')->all();

        $this->assertContains(ActionTicket::AFFECTATION, $actions);
        $this->assertContains(ActionTicket::CHANGEMENT_STATUT, $actions);
        $this->assertContains(ActionTicket::RESOLUTION, $actions);
        $this->assertContains(ActionTicket::CLOTURE, $actions);
    }

    public function test_references_uniques_et_sequentielles(): void
    {
        $premier = $this->declarerTicket($this->juridictionnaire);
        $second = $this->declarerTicket($this->juridictionnaire);

        $this->assertNotSame($premier->reference, $second->reference);
        $this->assertSame(2, Ticket::query()->count());
    }
}
