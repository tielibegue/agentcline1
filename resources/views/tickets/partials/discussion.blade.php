<div class="card">
    <h3>💬 Échanges ({{ $commentaires->count() }})</h3>

    @if($commentaires->isEmpty())
        <p class="empty">Aucun échange pour l’instant. Posez votre question ou apportez une précision ci-dessous.</p>
    @else
        <div class="timeline">
            @foreach($commentaires as $commentaire)
                <div class="message {{ $commentaire->interne ? 'message--internal' : '' }}">
                    <div class="head">
                        <span class="avatar">{{ $commentaire->auteur?->initiales() ?? '?' }}</span>
                        <span class="author">{{ $commentaire->auteur?->name ?? 'Compte supprimé' }}</span>
                        <span>{{ $commentaire->created_at?->format('d/m/Y à H:i') }}</span>
                        @if($commentaire->interne)
                            <span class="badge badge--purple">Note interne (support)</span>
                        @endif
                    </div>
                    <div class="body">{{ $commentaire->message }}</div>
                </div>
            @endforeach
        </div>
    @endif

    @can('commenter', $ticket)
        <form method="POST" action="{{ route('demandes.commentaires.store', $ticket) }}" style="margin-top:14px;">
            @csrf
            <div class="field">
                <label for="message">Ajouter un commentaire</label>
                <textarea id="message" name="message" required maxlength="4000"
                          placeholder="Écrivez ici votre message, votre question ou votre précision…"></textarea>
            </div>
            @if(auth()->user()->estInterne())
                <label class="checkline" style="margin:10px 0;">
                    <input type="checkbox" name="interne" value="1">
                    Note interne — visible uniquement par le support
                </label>
            @endif
            <button type="submit" class="btn btn--primary btn--small">Envoyer</button>
        </form>
    @endcan
</div>
