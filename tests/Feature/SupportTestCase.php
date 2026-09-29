<?php

namespace Tests\Feature;

use App\Models\Juridiction;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Usines de contexte métier partagées par les tests.
 */
abstract class SupportTestCase extends TestCase
{
    use RefreshDatabase;

    protected Juridiction $juridiction;

    protected User $admin;

    protected User $support;

    protected User $juridictionnaire;

    protected function setUp(): void
    {
        parent::setUp();

        $this->juridiction = Juridiction::factory()->create([
            'code' => 'TCA-ABJ',
            'libelle' => "Tribunal de commerce d'Abidjan",
            'type' => 'TRIBUNAL_COMMERCE',
            'ville' => 'Abidjan',
        ]);

        $this->admin = User::factory()->administrateur()->create(['email' => 'admin@support.local']);
        $this->support = User::factory()->support()->create(['email' => 'agent@support.local']);
        $this->juridictionnaire = User::factory()->create([
            'email' => 'greffe@tca.local',
            'juridiction_id' => $this->juridiction->id,
        ]);
    }

    protected function declarerTicket(User $declarant, array $attributs = []): Ticket
    {
        $ticket = Ticket::factory()->create($attributs);

        $ticket->forceFill([
            'juridiction_id' => $attributs['juridiction_id'] ?? $declarant->juridiction_id ?? $this->juridiction->id,
            'declarant_id' => $declarant->id,
        ])->save();

        $ticket->refresh();

        return $ticket;
    }
}
