@canany(['affecter', 'changerStatut', 'resoudre', 'rejeter', 'cloturer'], $ticket)
<div class="card">
    <h3>⚙ Traitement par le support</h3>
    <div class="form-grid">
        @can('affecter', $ticket)
            <div class="field">
                <form method="POST" action="{{ route('demandes.affecter', $ticket) }}">
                    @csrf
                    <label for="assigne_a_id">Affecter à un agent</label>
                    <div style="display:flex; gap:8px;">
                        <select id="assigne_a_id" name="assigne_a_id" style="flex:1;">
                            <option value="">— Retirer l’affectation —</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" @selected($ticket->assigne_a_id === $agent->id)>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn--primary btn--small">Affecter</button>
                    </div>
                </form>
            </div>
        @endcan

        @can('changerStatut', $ticket)
            <div class="field">
                <form method="POST" action="{{ route('demandes.statut', $ticket) }}">
                    @csrf
                    <label for="statut">Changer le statut</label>
                    <div style="display:flex; gap:8px;">
                        <select id="statut" name="statut" style="flex:1;">
                            @foreach($statutsModifiables as $code => $libelle)
                                <option value="{{ $code }}" @selected($ticket->statut->value === $code)>{{ $libelle }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn--primary btn--small">Appliquer</button>
                    </div>
                </form>
            </div>
        @endcan

        @can('resoudre', $ticket)
            <div class="field full">
                <form method="POST" action="{{ route('demandes.resoudre', $ticket) }}"
                      onsubmit="return confirm('Marquer la demande {{ $ticket->reference }} comme RÉSOLUE ?');">
                    @csrf
                    <label for="resolution">Consigner la résolution <span class="req">*</span></label>
                    <textarea id="resolution" name="resolution" required
                              placeholder="Décrivez la solution apportée (correctif, paramétrage, explication…) : ce texte sera visible par la juridiction."></textarea>
                    <div style="margin-top:8px;">
                        <button type="submit" class="btn btn--success">✔ Marquer comme résolue</button>
                    </div>
                </form>
            </div>
        @endcan

        @can('rejeter', $ticket)
            <div class="field full">
                <details>
                    <summary style="cursor:pointer; color:var(--danger); font-weight:600;">Rejeter la demande (doublon, hors périmètre…)</summary>
                    <form method="POST" action="{{ route('demandes.rejeter', $ticket) }}" style="margin-top:10px;"
                          onsubmit="return confirm('REJETER définitivement la demande {{ $ticket->reference }} ?');">
                        @csrf
                        <label for="motif_rejet">Motif du rejet <span class="req">*</span></label>
                        <textarea id="motif_rejet" name="motif_rejet" required
                                  placeholder="Expliquez pourquoi cette demande ne peut pas être traitée…"></textarea>
                        <div style="margin-top:8px;">
                            <button type="submit" class="btn btn--danger btn--small">Rejeter la demande</button>
                        </div>
                    </form>
                </details>
            </div>
        @endcan

        @can('cloturer', $ticket)
            <div class="field full">
                @if(auth()->user()->estInterne())
                    <form method="POST" action="{{ route('demandes.cloturer', $ticket) }}" class="inline-form">
                        @csrf
                        <input type="hidden" name="note" value="Clôture par le support.">
                        <button type="submit" class="btn btn--ghost btn--small">🔒 Clôturer la demande</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('demandes.cloturer', $ticket) }}">
                        @csrf
                        <label for="note_cloture">La résolution vous convient ? Confirmez la clôture.</label>
                        <div style="display:flex; gap:8px;">
                            <input type="text" id="note_cloture" name="note" style="flex:1;"
                                   placeholder="Ex. : Vérifié en production, je valide." maxlength="1000">
                            <button type="submit" class="btn btn--success btn--small">✔ Confirmer et clôturer</button>
                        </div>
                    </form>
                @endif
            </div>
        @endcan
    </div>
</div>
@endcanany
