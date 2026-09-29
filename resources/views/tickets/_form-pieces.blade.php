<div class="card">
    <h3>Pièces jointes <span class="muted" style="font-weight:normal;">(optionnel — 5 fichiers max, 10 Mo chacun)</span></h3>
    <div class="field">
        <label for="pieces_jointes">Captures d’écran, journaux, documents…</label>
        <input type="file" id="pieces_jointes" name="pieces_jointes[]" multiple
               accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.log,.zip">
        <span class="hint">Formats acceptés : images, PDF, Word, Excel, texte, journaux, ZIP.</span>
        @error('pieces_jointes')<span class="error">{{ $message }}</span>@enderror
        @error('pieces_jointes.*')<span class="error">{{ $message }}</span>@enderror
    </div>
</div>
