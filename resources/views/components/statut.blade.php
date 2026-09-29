@props(['code'])
@php
    use App\Enums\StatutTicket;
    $statut = $code instanceof StatutTicket ? $code : StatutTicket::tryFrom((string) $code);
@endphp
<span class="badge badge--{{ $statut?->couleur() ?? 'neutral' }}">{{ $statut?->label() ?? $code }}</span>
