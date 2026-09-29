@if($tickets->isEmpty())
    <p class="empty">Aucune demande ne correspond à ces critères.</p>
@else
    <div style="overflow-x:auto;">
        <table class="data">
            <thead>
            <tr>
                <th>Référence</th>
                <th>Objet</th>
                <th>Type</th>
                <th>Priorité</th>
                <th>Statut</th>
                <th>Juridiction</th>
                <th>Affecté à</th>
                <th>Déclarée le</th>
            </tr>
            </thead>
            <tbody>
            @foreach($tickets as $ticket)
                <tr @class(['late' => $ticket->estEnRetard()])>
                    <td><span class="ref">{{ $ticket->reference }}</span></td>
                    <td>
                        <a href="{{ route('demandes.show', $ticket) }}"><strong>{{ $ticket->titre }}</strong></a>
                        @if($ticket->estEnRetard())
                            <span class="badge badge--danger">En retard</span>
                        @endif
                        <br><span class="muted">{{ $ticket->libelleApplication() }}</span>
                    </td>
                    <td><x-type-ticket :code="$ticket->type" /></td>
                    <td><x-priorite :code="$ticket->priorite" /></td>
                    <td><x-statut :code="$ticket->statut" /></td>
                    <td>
                        {{ $ticket->juridiction?->libelle ?? '—' }}
                        @if($ticket->juridiction?->ville)
                            <br><span class="muted">{{ $ticket->juridiction->ville }}</span>
                        @endif
                    </td>
                    <td>{{ $ticket->assigneA?->name ?? '—' }}</td>
                    <td class="muted" style="white-space:nowrap;">{{ $ticket->created_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if(empty($sansPagination ?? false) && method_exists($tickets, 'links'))
        {{ $tickets->links() }}
    @endif
@endif
