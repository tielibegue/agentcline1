<?php

namespace Tests\Feature;

use App\Enums\ActionTicket;
use App\Enums\StatutTicket;

class ResolutionDemandeTest extends SupportTestCase
{
    public function test_le_support_affecte_une_demande(): void
    {
        $ticket = $this->declarerTicket($this->juridictionnaire);

        $this->actingAs($this->support)
            ->post('/demandes/'.$ticket->id.'/affecter', [
                'assigne_a_id' => $this->support->id,
                'note' => 'Prise en charge.',
            ])
            ->assertRedirect();

        $ticket->refresh();

        $this->assertSame($this->support->id, $ticket->assigne_a_id);
        $this->assertSame(StatutTicket::ASSIGNEE, $ticket->statut);
        $this->assertTrue($ticket->historiques()->where('action', ActionTicket::AFFECTATION)->exists());
    }

    public function test_le_support_fait_avancer_le_statut(): void
    {
        $ticket = $this->declarerTicket($this->juridictionnaire);

        $this->actingAs($this->support)
            ->post('/demandes/'.$ticket->id.'/statut', [
                'statut' => StatutTicket::EN_COURS->value,
                'note' => 'Diagnostic en cours.',
            ])
            ->assertRedirect();

        $this->assertSame(StatutTicket::EN_COURS, $ticket->refresh()->statut);
    }

    public function test_le_support_resout_une_demande(): void
    {
        $ticket = $this->declarerTicket($this->juridictionnaire);

        $this->actingAs($this->support)
            ->post('/demandes/'.$ticket->id.'/resoudre', [
                'resolution' => "Correctif déployé : l'impression du plumitif fonctionne à nouveau.",
            ])
            ->assertRedirect();

        $ticket->refresh();

        $this->assertSame(StatutTicket::RESOLUE, $ticket->statut);
        $this->assertSame($this->support->id, $ticket->resolu_par_id);
        $this->assertNotNull($ticket->resolu_le);
        $this->assertTrue($ticket->historiques()->where('action', ActionTicket::RESOLUTION)->exists());
    }

    public function test_le_support_rejette_avec_un_motif(): void
    {
        $ticket = $this->declarerTicket($this->juridictionnaire);

        $this->actingAs($this->support)
            ->post('/demandes/'.$ticket->id.'/rejeter', [
                'motif_rejet' => 'Doublon avec une demande déjà traitée par le support.',
            ])
            ->assertRedirect();

        $ticket->refresh();

        $this->assertSame(StatutTicket::REJETEE, $ticket->statut);
        $this->assertNotNull($ticket->motif_rejet);
    }

    public function test_la_juridiction_confirme_la_resolution_par_cloture(): void
    {
        $ticket = $this->declarerTicket($this->juridictionnaire, ['statut' => StatutTicket::RESOLUE]);

        $this->actingAs($this->juridictionnaire)
            ->post('/demandes/'.$ticket->id.'/cloturer', [
                'note' => 'Vérifié en production, je valide la résolution.',
            ])
            ->assertRedirect();

        $this->assertSame(StatutTicket::CLOTUREE, $ticket->refresh()->statut);
    }

    public function test_une_juridiction_ne_peut_pas_resoudre_elle_meme(): void
    {
        $ticket = $this->declarerTicket($this->juridictionnaire);

        $this->actingAs($this->juridictionnaire)
            ->post('/demandes/'.$ticket->id.'/resoudre', [
                'resolution' => 'Tentative de résolution par la juridiction.',
            ])
            ->assertForbidden();

        $this->assertSame(StatutTicket::NOUVELLE, $ticket->refresh()->statut);
    }
}
