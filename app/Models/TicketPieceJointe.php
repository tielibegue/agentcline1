<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_id', 'user_id', 'nom_original', 'chemin', 'type_mime', 'taille'])]
class TicketPieceJointe extends Model
{
    use HasFactory;

    protected $table = 'ticket_pieces_jointes';

    protected function casts(): array
    {
        return [
            'taille' => 'integer',
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

    /**
     * Taille lisible par un humain.
     */
    public function tailleLisible(): string
    {
        $octets = (int) $this->taille;

        if ($octets < 1024) {
            return $octets.' o';
        }

        if ($octets < 1024 * 1024) {
            return number_format($octets / 1024, 1, ',', ' ').' Ko';
        }

        return number_format($octets / (1024 * 1024), 2, ',', ' ').' Mo';
    }

    public function estImage(): bool
    {
        return is_string($this->type_mime) && str_starts_with($this->type_mime, 'image/');
    }
}
