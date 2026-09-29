@extends('layouts.app')

@section('titre', 'Tableau de bord')

@section('contenu')
<div class="kpis">
    <div class="kpi">
        <div class="value">{{ $total }}</div>
        <div class="label">Demandes au total</div>
    </div>
    <div class="kpi kpi--warning">
        <div class="value">{{ $ouverts }}</div>
        <div class="label">Encore ouvertes</div>
    </div>
    <div class="kpi kpi--danger">
        <div class="value">{{ $enRetard }}</div>
        <div class="label">En retard de traitement</div>
    </div>
    @if(auth()->user()->estInterne())
        <div class="kpi kpi--purple">
            <div class="value">{{ $nonAffectes }}</div>
            <div class="label">À affecter</div>
        </div>
    @endif
    <div class="kpi kpi--success">
        <div class="value">{{ $resolus }}</div>
        <div class="label">Résolues</div>
        <div class="sub">{{ $tauxResolution }} % de résolution</div>
    </div>
    @if($delaiMoyenHeures !== null)
        <div class="kpi kpi--neutral">
            <div class="value">{{ number_format($delaiMoyenHeures, 1, ',', ' ') }} h</div>
            <div class="label">Délai moyen de résolution (mois en cours)</div>
        </div>
    @endif
</div>

<div class="grid-2">
    <div class="card">
        <h3>Par statut</h3>
        @if(count($parStatut) > 0)
            <table class="data">
                <thead><tr><th>Statut</th><th style="text-align:right;">Demandes</th></tr></thead>
                <tbody>
                @foreach($parStatut as $code => $nombre)
                    <tr>
                        <td><x-statut :code="$code" /></td>
                        <td style="text-align:right;"><strong>{{ $nombre }}</strong></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="empty">Aucune demande pour l’instant.</p>
        @endif
    </div>

    <div class="card">
        <h3>Par type</h3>
        @if(count($parType) > 0)
            <table class="data">
                <thead><tr><th>Type</th><th style="text-align:right;">Demandes</th></tr></thead>
                <tbody>
                @foreach($parType as $code => $nombre)
                    <tr>
                        <td><x-type-ticket :code="$code" /></td>
                        <td style="text-align:right;"><strong>{{ $nombre }}</strong></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="empty">Aucune demande pour l’instant.</p>
        @endif
    </div>
</div>

@if(auth()->user()->estInterne() && $ticketsAssignes->isNotEmpty())
    <div class="card">
        <h3>Mes demandes en charge</h3>
        @include('tickets.partials.tableau', ['tickets' => $ticketsAssignes, 'sansPagination' => true])
    </div>
@endif

<div class="card">
    <h3>Dernières demandes</h3>
    @if($derniersTickets->isNotEmpty())
        @include('tickets.partials.tableau', ['tickets' => $derniersTickets, 'sansPagination' => true])
        <p style="margin:12px 0 0;"><a href="{{ route('demandes.index') }}">Voir toutes les demandes →</a></p>
    @else
        <p class="empty">Aucune demande déclarée pour le moment.</p>
    @endif
</div>

@if(auth()->user()->estInterne() && $topJuridictions->isNotEmpty())
    <div class="card">
        <h3>Juridictions les plus actives</h3>
        <table class="data">
            <thead><tr><th>Juridiction</th><th style="text-align:right;">Demandes</th><th style="text-align:right;">Ouvertes</th></tr></thead>
            <tbody>
            @foreach($topJuridictions as $juridiction)
                <tr>
                    <td><strong>{{ $juridiction->libelle }}</strong><br><span class="muted">{{ $juridiction->ville }}</span></td>
                    <td style="text-align:right;">{{ $juridiction->tickets_count }}</td>
                    <td style="text-align:right;">{{ $juridiction->tickets_ouverts_count }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
