<div class="card">
    <h3>Contexte technique</h3>
    <div class="form-grid">
        @if(!$modification && auth()->user()->estInterne())
            <div class="field">
                <label for="juridiction_id">Juridiction concernée</label>
                <select id="juridiction_id" name="juridiction_id">
                    <option value="">— Sélectionner —</option>
                    @foreach($juridictions as $juridiction)
                        <option value="{{ $juridiction->id }}" @selected((string)old('juridiction_id') === (string)$juridiction->id)>
                            {{ $juridiction->libelle }}{{ $juridiction->ville ? ' ('.$juridiction->ville.')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('juridiction_id')<span class="error">{{ $message }}</span>@enderror
            </div>
        @endif

        <div class="field">
            <label for="application">Application concernée</label>
            <select id="application" name="application">
                <option value="">— Sélectionner —</option>
                @foreach($applications as $code => $libelle)
                    <option value="{{ $code }}" @selected(old('application', $modification ? ($ticket->application ?? '') : '') === $code)>{{ $libelle }}</option>
                @endforeach
            </select>
            @error('application')<span class="error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <label for="module_fonctionnel">Module / écran concerné</label>
            <input type="text" id="module_fonctionnel" name="module_fonctionnel" maxlength="255"
                   value="{{ old('module_fonctionnel', $modification ? ($ticket->module_fonctionnel ?? '') : '') }}"
                   placeholder="Ex. : ui/aj/depot, ui/aj/nc…">
            @error('module_fonctionnel')<span class="error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <label for="version_application">Version de l’application</label>
            <input type="text" id="version_application" name="version_application" maxlength="50"
                   value="{{ old('version_application', $modification ? ($ticket->version_application ?? '') : '') }}"
                   placeholder="Ex. : E-Trib_Com 2.4">
            @error('version_application')<span class="error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <label for="environnement">Environnement / poste</label>
            <input type="text" id="environnement" name="environnement" maxlength="255"
                   value="{{ old('environnement', $modification ? ($ticket->environnement ?? '') : '') }}"
                   placeholder="Ex. : PC greffe civil n°3, Windows 11">
            @error('environnement')<span class="error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <label for="date_incident">Date de l’incident</label>
            <input type="date" id="date_incident" name="date_incident" max="{{ date('Y-m-d') }}"
                   value="{{ old('date_incident', $modification && $ticket->date_incident ? $ticket->date_incident->format('Y-m-d') : '') }}">
            @error('date_incident')<span class="error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <label>&nbsp;</label>
            <label class="checkline">
                <input type="checkbox" name="reproductible" value="1"
                       @checked((bool)old('reproductible', $modification ? $ticket->reproductible : false))>
                Le problème est reproductible à volonté
            </label>
        </div>
    </div>
</div>
