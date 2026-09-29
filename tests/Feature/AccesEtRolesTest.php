<?php

namespace Tests\Feature;

use App\Models\Juridiction;
use App\Models\User;

class AccesEtRolesTest extends SupportTestCase
{
    public function test_une_juridiction_ne_voit_que_ses_demandes(): void
    {
        $autreJuridiction = Juridiction::factory()->create(['code' => 'TCA-BKE']);
        $autreContact = User::factory()->create(['juridiction_id' => $autreJuridiction->id]);

        $mienne = $this->declarerTicket($this->juridictionnaire);
        $autre = $this->declarerTicket($autreContact);

        $this->actingAs($this->juridictionnaire)->get('/demandes/'.$mienne->id)->assertOk();
        $this->actingAs($this->juridictionnaire)->get('/demandes/'.$autre->id)->assertForbidden();
    }

    public function test_le_support_voit_toutes_les_demandes(): void
    {
        $ticket = $this->declarerTicket($this->juridictionnaire);

        $reponse = $this->actingAs($this->support)->get('/demandes');

        $reponse->assertOk();
        $reponse->assertSee($ticket->reference);
    }

    public function test_seul_l_admin_gere_les_juridictions(): void
    {
        $this->actingAs($this->support)->get('/juridictions')->assertForbidden();
        $this->actingAs($this->admin)->get('/juridictions')->assertOk();
        $this->actingAs($this->juridictionnaire)->get('/juridictions')->assertForbidden();
    }

    public function test_seul_l_admin_gere_les_comptes(): void
    {
        $this->actingAs($this->support)->get('/utilisateurs')->assertForbidden();
        $this->actingAs($this->admin)->get('/utilisateurs')->assertOk();
        $this->actingAs($this->juridictionnaire)->get('/utilisateurs')->assertForbidden();
    }

    public function test_l_admin_ne_peut_pas_supprimer_son_propre_compte(): void
    {
        $this->actingAs($this->admin)
            ->delete('/utilisateurs/'.$this->admin->id)
            ->assertForbidden();

        $this->assertNotNull(User::query()->find($this->admin->id));
    }

    public function test_une_juridiction_sans_liste_globale(): void
    {
        // Les filtres internes ne sont pas proposés aux juridictions.
        $reponse = $this->actingAs($this->juridictionnaire)->get('/demandes');

        $reponse->assertOk();
        $reponse->assertDontSee('Affecté à', false);
    }
}
