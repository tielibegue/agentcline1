{{-- Formulaire partagé : déclaration (création) et modification (support). --}}
@php
    $modification = isset($ticket) && $ticket->exists;
@endphp

<form method="POST"
      action="{{ $modification ? route('demandes.update', $ticket) : route('demandes.store') }}"
      @if(!$modification) enctype="multipart/form-data" @endif>
    @csrf
    @if($modification) @method('PUT') @endif

    <div class="card">
        <h3>{{ $modification ? 'Contenu de la demande '.$ticket->reference : 'Nature de la demande' }}</h3>
        <div class="form-grid">
            <div class="field">
                <label for="type">Type de demande <span class="req">*</span></label>
                <select id="type" name="type" required>
                    @foreach($types as $code => $libelle)
                        <option value="{{ $code }}" @selected(old('type', $modification ? $ticket->type->value : '') === $code)>{{ $libelle }}</option>
                    @endforeach
                </select>
                @error('type')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="priorite">Priorité <span class="req">*</span></label>
                <select id="priorite" name="priorite" required>
                    @foreach($priorites as $code => $libelle)
                        <option value="{{ $code }}" @selected(old('priorite', $modification ? $ticket->priorite->value : 'MOYENNE') === $code)>{{ $libelle }}</option>
                    @endforeach
                </select>
                @error('priorite')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="field full">
                <label for="titre">Objet de la demande <span class="req">*</span></label>
                <input type="text" id="titre" name="titre" required maxlength="180"
                       value="{{ old('titre', $modification ? $ticket->titre : '') }}"
                       placeholder="Ex. : Impression du plumitif impossible sur E-TribCom">
                @error('titre')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="field full">
                <label for="description">Description détaillée <span class="req">*</span></label>
                <textarea id="description" name="description" required
                          placeholder="Décrivez précisément le problème : ce que vous faisiez, ce qui s’est produit, le message d’erreur affiché…">{{ old('description', $modification ? $ticket->description : '') }}</textarea>
                <span class="hint">20 caractères minimum : plus la description est précise, plus la résolution est rapide.</span>
                @error('description')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    @include('tickets._form-contexte')

    @if(!$modification)
        @include('tickets._form-pieces')
    @endif

    <div class="toolbar">
        <button type="submit" class="btn btn--primary">{{ $modification ? 'Enregistrer les modifications' : 'Déclarer la demande' }}</button>
        <a href="{{ $modification ? route('demandes.show', $ticket) : route('demandes.index') }}" class="btn btn--ghost">Annuler</a>
    </div>
</form>
