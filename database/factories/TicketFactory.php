<?php

namespace Database\Factories;

use App\Enums\PrioriteTicket;
use App\Enums\TypeTicket;
use App\Models\Juridiction;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(TypeTicket::cases()),
            'priorite' => fake()->randomElement(PrioriteTicket::cases()),
            'titre' => fake()->sentence(8),
            'description' => fake()->paragraphs(2, true),
            'application' => fake()->randomElement(array_keys(Ticket::APPLICATIONS)),
            'module_fonctionnel' => fake()->randomElement(['ui/aj/depot', 'ui/aj/nc', 'ui/aj/co', 'ui/aj/traitementRequete', 'ui/rc/depot']),
            'date_incident' => fake()->dateTimeBetween('-15 days', 'now'),
        ];
    }

    /**
     * Rattache la demande à un déclarant (et à sa juridiction).
     */
    public function declarePar(User $declarant, ?Juridiction $juridiction = null): static
    {
        return $this->afterCreating(function (Ticket $ticket) use ($declarant, $juridiction): void {
            $ticket->forceFill([
                'juridiction_id' => $juridiction?->id ?? $declarant->juridiction_id,
                'declarant_id' => $declarant->id,
            ])->save();
        });
    }
}
