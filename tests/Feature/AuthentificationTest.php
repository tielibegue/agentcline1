<?php

namespace Tests\Feature;

use App\Models\User;

class AuthentificationTest extends SupportTestCase
{
    public function test_un_invite_est_redirige_vers_la_connexion(): void
    {
        $this->get('/tableau-de-bord')->assertRedirect('/connexion');
        $this->get('/demandes')->assertRedirect('/connexion');
    }

    public function test_page_de_connexion_accessible(): void
    {
        $this->get('/connexion')->assertOk()->assertSee('Connexion');
    }

    public function test_un_utilisateur_actif_peut_se_connecter(): void
    {
        $reponse = $this->post('/connexion', [
            'email' => $this->support->email,
            'password' => 'password',
        ]);

        $reponse->assertRedirect('/tableau-de-bord');
        $this->assertAuthenticatedAs($this->support);
    }

    public function test_un_compte_desactive_ne_peut_pas_se_connecter(): void
    {
        $inactif = User::factory()->support()->inactif()->create();

        $this->post('/connexion', [
            'email' => $inactif->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_trop_de_tentatives_bloquent_le_compte(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/connexion', [
                'email' => $this->support->email,
                'password' => 'mauvais-mot-de-passe',
            ]);
        }

        $this->post('/connexion', [
            'email' => $this->support->email,
            'password' => 'mauvais-mot-de-passe',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deconnexion(): void
    {
        $this->actingAs($this->support)
            ->post('/deconnexion')
            ->assertRedirect('/connexion');

        $this->assertGuest();
    }
}
