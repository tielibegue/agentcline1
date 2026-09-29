<?php

namespace App\Models;

use App\Enums\ActionTicket;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_id', 'user_id', 'action', 'ancienne_valeur', 'nouvelle_valeur', 'commentaire'])]
class TicketHistorique extends Model
{
    use HasFactory;

    protected $table = 'ticket_historiques';

    protected function casts(): array
    {
        return [
            'action' => ActionTicket::class,
        ];
    }

    /**
     * @return BelongsTo<Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function libelleAction(): string
    {
        return $this->action instanceof ActionTicket ? $this->action->label() : (string) $this->action;
    }
}
