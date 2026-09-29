@extends('layouts.app')

@section('titre', isset($juridiction) ? 'Modifier '.$juridiction->libelle : 'Nouvelle juridiction')

@section('contenu')
<form method="POST" action="{{ isset($juridiction) ? route('juridictions.update', $juridiction) : route('juridictions.store') }}">
    @csrf
    @if(isset($juridiction)) @method('PUT') @endif

    <div class="card">
        <div class="form-grid">
            <div class="field">
                <label for="code">Code <span class="req">*</span></label>
                <input type="text" id="code" name="code" required maxlength="30"
                       value="{{ old('code', $juridiction->code ?? '') }}" placeholder="Ex. : TCA-ABJ"
                       @if(isset($juridiction)) @endif>
                <span class="hint">Identifiant court, sans espace (lettres, chiffres, tirets).</span>
                @error('code')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="type">Type <span class="req">*</span></label>
                <select id="type" name="type" required>
                    @foreach($types as $code => $libelle)
                        <option value="{{ $code }}" @selected(old('type', $juridiction->type ?? 'TRIBUNAL_COMMERCE') === $code)>{{ $libelle }}</option>
                    @endforeach
                </select>
                @error('type')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field full">
                <label for="libelle">Libellé <span class="req">*</span></label>
                <input type="text" id="libelle" name="libelle" required maxlength="255"
                       value="{{ old('libelle', $juridiction->libelle ?? '') }}"
                       placeholder="Ex. : Tribunal de commerce d’Abidjan">
                @error('libelle')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="ville">Ville</label>
                <input type="text" id="ville" name="ville" maxlength="100" value="{{ old('ville', $juridiction->ville ?? '') }}">
                @error('ville')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="commune">Commune</label>
                <input type="text" id="commune" name="commune" maxlength="100" value="{{ old('commune', $juridiction->commune ?? '') }}">
                @error('commune')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="responsable">Responsable</label>
                <input type="text" id="responsable" name="responsable" maxlength="255" value="{{ old('responsable', $juridiction->responsable ?? '') }}">
                @error('responsable')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="telephone">Téléphone</label>
                <input type="text" id="telephone" name="telephone" maxlength="30" value="{{ old('telephone', $juridiction->telephone ?? '') }}">
                @error('telephone')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field full">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" maxlength="255" value="{{ old('email', $juridiction->email ?? '') }}">
                @error('email')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label>&nbsp;</label>
                <label class="checkline">
                    <input type="checkbox" name="actif" value="1"
                           @checked((bool)old('actif', $juridiction->actif ?? true))>
                    Juridiction active
                </label>
            </div>
        </div>
    </div>

    <div class="toolbar">
        <button type="submit" class="btn btn--primary">{{ isset($juridiction) ? 'Enregistrer' : 'Créer la juridiction' }}</button>
        <a href="{{ route('juridictions.index') }}" class="btn btn--ghost">Annuler</a>
    </div>
</form>
@endsection
