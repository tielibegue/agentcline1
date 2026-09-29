@props(['code'])
@php
    use App\Enums\PrioriteTicket;
    $priorite = $code instanceof PrioriteTicket ? $code : PrioriteTicket::tryFrom((string) $code);
@endphp
<span class="badge badge--{{ $priorite?->couleur() ?? 'neutral' }}">{{ $priorite?->label() ?? $code }}</span>
