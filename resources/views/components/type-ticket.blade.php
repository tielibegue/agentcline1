@props(['code'])
@php
    use App\Enums\TypeTicket;
    $type = $code instanceof TypeTicket ? $code : TypeTicket::tryFrom((string) $code);
@endphp
<span class="badge badge--{{ $type?->couleur() ?? 'neutral' }}">{{ $type?->label() ?? $code }}</span>
