<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_la_racine_redirige_vers_le_tableau_de_bord(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/tableau-de-bord');
    }
}
