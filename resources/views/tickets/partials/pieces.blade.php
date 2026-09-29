<div class="card">
    <h3>📎 Pièces jointes ({{ $pieces->count() }})</h3>

    @if($pieces->isEmpty())
        <p class="empty">Aucune pièce jointe pour l’instant.</p>
    @else
        <table class="data">
            <thead><tr><th>Fichier</th><th>Taille</th><th>Ajouté par</th><th>Le</th><th></th></tr></thead>
            <tbody>
            @foreach($pieces as $piece)
                <tr>
                    <td><strong>{{ $piece->nom_original }}</strong><br><span class="muted">{{ $piece->type_mime }}</span></td>
                    <td>{{ $piece->tailleLisible() }}</td>
                    <td>{{ $piece->auteur?->name ?? '—' }}</td>
                    <td class="muted">{{ $piece->created_at?->format('d/m/Y H:i') }}</td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('demandes.pieces.download', [$ticket, $piece]) }}" class="btn btn--ghost btn--small">⬇ Télécharger</a>
                        @can('supprimerPieceJointe', $ticket)
                            <form method="POST" action="{{ route('demandes.pieces.destroy', [$ticket, $piece]) }}" class="inline-form"
                                  onsubmit="return confirm('Supprimer {{ $piece->nom_original }} ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn--link" style="color:var(--danger);">Supprimer</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    @can('ajouterPieceJointe', $ticket)
        <form method="POST" action="{{ route('demandes.pieces.store', $ticket) }}" enctype="multipart/form-data" style="margin-top:14px;">
            @csrf
            <div style="display:flex; gap:8px; align-items:end; flex-wrap:wrap;">
                <div class="field" style="flex:1; min-width:240px;">
                    <label for="pj">Ajouter des pièces (5 max, 10 Mo chacune)</label>
                    <input type="file" id="pj" name="pieces_jointes[]" multiple required
                           accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.log,.zip">
                </div>
                <button type="submit" class="btn btn--primary btn--small">Joindre</button>
            </div>
        </form>
    @endcan
</div>
