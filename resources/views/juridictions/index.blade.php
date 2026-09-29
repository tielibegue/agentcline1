@extends('layouts.app')

@section('titre', 'Juridictions')

@section('contenu')
<div class="toolbar">
    <a href="{{ route('juridictions.create') }}" class="btn btn--primary">➕ Nouvelle juridiction</a>
    <div class="spacer"></div>
    <form method="GET" action="{{ route('juridictions.index') }}">
        <div style="display:flex; gap:8px;">
            <input type="text" name="recherche" value="{{ $recherche }}" placeholder="Rechercher…" style="width:240px;">
            <button type="submit" class="btn btn--ghost btn--small">Rechercher</button>
        </div>
    </form>
</div>

<div class="card">
    @if($juridictions->isEmpty())
        <p class="empty">Aucune juridiction enregistrée.</p>
    @else
        <table class="data">
            <thead>
            <tr><th>Code</th><th>Libellé</th><th>Type</th><th>Ville</th><th>Contact</th><th style="text-align:right;">Demandes (ouvertes)</th><th>État</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($juridictions as $juridiction)
                <tr>
                    <td><span class="ref">{{ $juridiction->code }}</span></td>
                    <td><strong>{{ $juridiction->libelle }}</strong>
                        @if($juridiction->commune)<br><span class="muted">{{ $juridiction->commune }}</span>@endif</td>
                    <td>{{ $juridiction->libelleType() }}</td>
                    <td>{{ $juridiction->ville ?? '—' }}</td>
                    <td class="muted">{{ $juridiction->telephone ?? '—' }}<br>{{ $juridiction->email ?? '' }}</td>
                    <td style="text-align:right;">{{ $juridiction->tickets_count }} ({{ $juridiction->tickets_ouverts_count }})</td>
                    <td>
                        @if($juridiction->actif)
                            <span class="badge badge--success">Active</span>
                        @else
                            <span class="badge badge--neutral">Inactive</span>
                        @endif
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('juridictions.edit', $juridiction) }}" class="btn btn--ghost btn--small">Modifier</a>
                        <form method="POST" action="{{ route('juridictions.destroy', $juridiction) }}" class="inline-form"
                              onsubmit="return confirm('Supprimer {{ $juridiction->libelle }} ?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn--link" style="color:var(--danger);">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $juridictions->links() }}
    @endif
</div>
@endsection
