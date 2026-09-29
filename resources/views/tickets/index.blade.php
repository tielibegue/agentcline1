@extends('layouts.app')

@section('titre', 'Demandes')

@section('contenu')
<div class="toolbar">
    <a href="{{ route('demandes.create') }}" class="btn btn--primary">➕ Nouvelle demande</a>
    <div class="spacer"></div>
    <a href="{{ route('demandes.export', request()->query()) }}" class="btn btn--ghost">⬇ Exporter (CSV)</a>
</div>

<div class="card">
    <form method="GET" action="{{ route('demandes.index') }}">
        <div class="filters">
            <div class="field field--search">
                <label for="recherche">Recherche</label>
                <input type="text" id="recherche" name="recherche" value="{{ $filtres['recherche'] ?? '' }}" placeholder="Référence, objet, description…">
            </div>
            <div class="field">
                <label for="statut">Statut</label>
                <select id="statut" name="statut">
                    <option value="">Tous</option>
                    @foreach($statuts as $code => $libelle)
                        <option value="{{ $code }}" @selected(($filtres['statut'] ?? '') === $code)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="type">Type</label>
                <select id="type" name="type">
                    <option value="">Tous</option>
                    @foreach($types as $code => $libelle)
                        <option value="{{ $code }}" @selected(($filtres['type'] ?? '') === $code)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="priorite">Priorité</label>
                <select id="priorite" name="priorite">
                    <option value="">Toutes</option>
                    @foreach($priorites as $code => $libelle)
                        <option value="{{ $code }}" @selected(($filtres['priorite'] ?? '') === $code)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="application">Application</label>
                <select id="application" name="application">
                    <option value="">Toutes</option>
                    @foreach($applications as $code => $libelle)
                        <option value="{{ $code }}" @selected(($filtres['application'] ?? '') === $code)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            @if(auth()->user()->estInterne())
                <div class="field">
                    <label for="juridiction_id">Juridiction</label>
                    <select id="juridiction_id" name="juridiction_id">
                        <option value="">Toutes</option>
                        @foreach($juridictions as $juridiction)
                            <option value="{{ $juridiction->id }}" @selected((string)($filtres['juridiction_id'] ?? '') === (string)$juridiction->id)>
                                {{ $juridiction->libelle }}{{ $juridiction->ville ? ' ('.$juridiction->ville.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="assigne_a_id">Affecté à</label>
                    <select id="assigne_a_id" name="assigne_a_id">
                        <option value="">Tous</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" @selected((string)($filtres['assigne_a_id'] ?? '') === (string)$agent->id)>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="checkline" title="Afficher uniquement les demandes ouvertes">
                    <input type="checkbox" name="ouverts_seulement" value="1" @checked(!empty($filtres['ouverts_seulement']))> Ouvertes seulement
                </label>
                <label class="checkline" title="Afficher uniquement les demandes sans agent affecté">
                    <input type="checkbox" name="non_affectes" value="1" @checked(!empty($filtres['non_affectes']))> Non affectées
                </label>
            @endif
            <div class="field">
                <label for="tri">Trier par</label>
                <select id="tri" name="tri">
                    <option value="recent" @selected($tri === 'recent')>Plus récentes</option>
                    <option value="echeance" @selected($tri === 'echeance')>Échéance</option>
                    <option value="priorite" @selected($tri === 'priorite')>Priorité</option>
                    <option value="statut" @selected($tri === 'statut')>Statut</option>
                    <option value="titre" @selected($tri === 'titre')>Objet</option>
                </select>
            </div>
            <div class="actions">
                <button type="submit" class="btn btn--primary btn--small">Filtrer</button>
                <a href="{{ route('demandes.index') }}" class="btn btn--ghost btn--small">Réinitialiser</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    @include('tickets.partials.tableau', ['tickets' => $tickets])
</div>
@endsection
