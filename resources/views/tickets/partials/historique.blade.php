<div class="card">
    <h3>🕘 Historique de la demande ({{ $historiques->count() }})</h3>

    @if($historiques->isEmpty())
        <p class="empty">Aucun événement enregistré.</p>
    @else
        <ul class="history">
            @foreach($historiques as $evenement)
                <li>
                    <span class="dot" style="background: var(--{{ $evenement->action->couleur() }});"></span>
                    <div>
                        <strong>{{ $evenement->libelleAction() }}</strong>
                        @if($evenement->ancienne_valeur || $evenement->nouvelle_valeur)
                            <span class="muted">
                                @if($evenement->ancienne_valeur)« {{ $evenement->ancienne_valeur }} » → @endif
                                @if($evenement->nouvelle_valeur)<strong>{{ $evenement->nouvelle_valeur }}</strong>@endif
                            </span>
                        @endif
                        @if($evenement->commentaire)
                            <div class="muted">{{ \Illuminate\Support\Str::limit($evenement->commentaire, 300) }}</div>
                        @endif
                        <div class="who">par {{ $evenement->auteur?->name ?? 'le système' }}</div>
                    </div>
                    <span class="when">{{ $evenement->created_at?->format('d/m/Y H:i') }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
