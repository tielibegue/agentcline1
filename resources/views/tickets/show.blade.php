@extends('layouts.app')

@section('titre', $ticket->reference)

@section('contenu')
<div class="detail-head">
    <div style="flex:1; min-width:260px;">
        <span class="ref" style="font-size:15px;">{{ $ticket->reference }}</span>
        <h2>{{ $ticket->titre }}</h2>
        <div class="badges">
            <x-statut :code="$ticket->statut" />
            <x-type-ticket :code="$ticket->type" />
            <x-priorite :code="$ticket->priorite" />
            @if($ticket->estEnRetard())
                <span class="badge badge--danger">⚠ En retard</span>
            @endif
        </div>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="{{ route('demandes.index') }}" class="btn btn--ghost btn--small">← Retour à la liste</a>
        @can('update', $ticket)
            <a href="{{ route('demandes.edit', $ticket) }}" class="btn btn--ghost btn--small">✏ Modifier</a>
        @endcan
        @can('delete', $ticket)
            <form method="POST" action="{{ route('demandes.destroy', $ticket) }}" class="inline-form"
                  onsubmit="return confirm('Supprimer la demande {{ $ticket->reference }} (mise en corbeille) ?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn--danger btn--small">🗑 Supprimer</button>
            </form>
        @endcan
    </div>
</div>

@if($ticket->estEnRetard())
    <div class="late-banner">
        ⚠ Cette demande a dépassé son délai de traitement attendu
        ({{ $ticket->delaiResolutionHeures() }} h, échéance le {{ $ticket->dateEcheance()?->format('d/m/Y à H:i') }}).
    </div>
@endif

<div class="card">
    <h3>Description de la demande</h3>
    <div class="description">{{ $ticket->description }}</div>

    <div style="margin-top:18px;">
        <dl class="kv">
            <div><dt>Juridiction</dt><dd>{{ $ticket->juridiction?->nomComplet() ?? '—' }}</dd></div>
            <div><dt>Déclarée par</dt><dd>{{ $ticket->declarant?->name ?? '—' }}<br>
                <span class="muted" style="font-weight:normal;">{{ $ticket->created_at?->format('d/m/Y à H:i') }}</span></dd></div>
            <div><dt>Application</dt><dd>{{ $ticket->libelleApplication() }}</dd></div>
            <div><dt>Module / écran</dt><dd>{{ $ticket->module_fonctionnel ?? '—' }}</dd></div>
            <div><dt>Version</dt><dd>{{ $ticket->version_application ?? '—' }}</dd></div>
            <div><dt>Environnement</dt><dd>{{ $ticket->environnement ?? '—' }}</dd></div>
            <div><dt>Date de l’incident</dt><dd>{{ $ticket->date_incident?->format('d/m/Y') ?? '—' }}</dd></div>
            <div><dt>Reproductible</dt><dd>{{ $ticket->reproductible === null ? 'Non précisé' : ($ticket->reproductible ? 'Oui' : 'Non') }}</dd></div>
            <div><dt>Affectée à</dt><dd>{{ $ticket->assigneA?->name ?? 'Pas encore affectée' }}
                @if($ticket->affecte_le)<br><span class="muted" style="font-weight:normal;">{{ $ticket->affecte_le->format('d/m/Y à H:i') }}</span>@endif</dd></div>
            <div><dt>Échéance indicative</dt><dd>{{ $ticket->dateEcheance()?->format('d/m/Y à H:i') ?? '—' }}</dd></div>
        </dl>
    </div>
</div>

@if($ticket->resolution)
    <div class="card">
        <h3>✅ Résolution apportée par le support</h3>
        <div class="resolution-box">{{ $ticket->resolution }}</div>
        <p class="muted" style="font-size:13px;">Par {{ $ticket->resoluPar?->name ?? '—' }}, le {{ $ticket->resolu_le?->format('d/m/Y à H:i') }}.</p>
    </div>
@endif

@if($ticket->motif_rejet)
    <div class="card">
        <h3>⛔ Motif du rejet</h3>
        <div class="rejet-box">{{ $ticket->motif_rejet }}</div>
    </div>
@endif

@include('tickets.partials.traitement')

@include('tickets.partials.pieces')

@include('tickets.partials.discussion')

@include('tickets.partials.historique')
@endsection
