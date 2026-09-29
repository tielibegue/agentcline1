@extends('layouts.app')

@section('titre', isset($utilisateur) ? 'Modifier '.$utilisateur->email : 'Nouveau compte')

@section('contenu')
<form method="POST" action="{{ isset($utilisateur) ? route('utilisateurs.update', $utilisateur) : route('utilisateurs.store') }}">
    @csrf
    @if(isset($utilisateur)) @method('PUT') @endif

    <div class="card">
        <div class="form-grid">
            <div class="field">
                <label for="name">Nom complet <span class="req">*</span></label>
                <input type="text" id="name" name="name" required maxlength="255"
                       value="{{ old('name', $utilisateur->name ?? '') }}">
                @error('name')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="email">Adresse e-mail <span class="req">*</span></label>
                <input type="email" id="email" name="email" required maxlength="255"
                       value="{{ old('email', $utilisateur->email ?? '') }}">
                @error('email')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="password">Mot de passe {{ isset($utilisateur) ? '(laisser vide pour inchangé)' : '*' }}</label>
                <input type="password" id="password" name="password" autocomplete="new-password"
                       @if(!isset($utilisateur)) required @endif>
                <span class="hint">8 caractères minimum, avec lettres et chiffres.</span>
                @error('password')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="password_confirmation">Confirmation du mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
            </div>
            <div class="field">
                <label for="role">Rôle <span class="req">*</span></label>
                <select id="role" name="role" required onchange="document.getElementById('bloc-juridiction').style.display = this.value === 'JURIDICTION' ? '' : 'none';">
                    @foreach($roles as $code => $libelle)
                        <option value="{{ $code }}" @selected(old('role', $utilisateur->role->value ?? 'JURIDICTION') === $code)>{{ $libelle }}</option>
                    @endforeach
                </select>
                @error('role')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field" id="bloc-juridiction" style="display:{{ old('role', $utilisateur->role->value ?? 'JURIDICTION') === 'JURIDICTION' ? '' : 'none' }};">
                <label for="juridiction_id">Juridiction de rattachement</label>
                <select id="juridiction_id" name="juridiction_id">
                    <option value="">— Sélectionner —</option>
                    @foreach($juridictions as $juridiction)
                        <option value="{{ $juridiction->id }}" @selected((string)old('juridiction_id', isset($utilisateur) ? (string)($utilisateur->juridiction_id ?? '') : '') === (string)$juridiction->id)>
                            {{ $juridiction->libelle }}{{ $juridiction->ville ? ' ('.$juridiction->ville.')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('juridiction_id')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="fonction">Fonction</label>
                <input type="text" id="fonction" name="fonction" maxlength="100"
                       value="{{ old('fonction', $utilisateur->fonction ?? '') }}" placeholder="Ex. : Greffier en chef">
                @error('fonction')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="telephone">Téléphone</label>
                <input type="text" id="telephone" name="telephone" maxlength="30"
                       value="{{ old('telephone', $utilisateur->telephone ?? '') }}">
                @error('telephone')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field full">
                <label>&nbsp;</label>
                <label class="checkline">
                    <input type="checkbox" name="actif" value="1"
                           @checked((bool)old('actif', $utilisateur->actif ?? true))
                           @if(isset($utilisateur) && $utilisateur->id === auth()->id()) disabled @endif>
                    Compte actif (un compte désactivé ne peut plus se connecter)
                </label>
            </div>
        </div>
    </div>

    <div class="toolbar">
        <button type="submit" class="btn btn--primary">{{ isset($utilisateur) ? 'Enregistrer' : 'Créer le compte' }}</button>
        <a href="{{ route('utilisateurs.index') }}" class="btn btn--ghost">Annuler</a>
    </div>
</form>
@endsection
