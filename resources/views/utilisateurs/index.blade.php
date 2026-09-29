@extends('layouts.app')

@section('titre', 'Comptes utilisateurs')

@section('contenu')
<div class="toolbar">
    <a href="{{ route('utilisateurs.create') }}" class="btn btn--primary">➕ Nouveau compte</a>
    <div class="spacer"></div>
    <form method="GET" action="{{ route('utilisateurs.index') }}">
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <select name="role" style="width:200px;">
                <option value="">Tous les rôles</option>
                @foreach($roles as $code => $libelle)
                    <option value="{{ $code }}" @selected($role === $code)>{{ $libelle }}</option>
                @endforeach
            </select>
            <input type="text" name="recherche" value="{{ $recherche }}" placeholder="Nom ou e-mail…" style="width:220px;">
            <button type="submit" class="btn btn--ghost btn--small">Rechercher</button>
        </div>
    </form>
</div>

<div class="card">
    @if($utilisateurs->isEmpty())
        <p class="empty">Aucun compte enregistré.</p>
    @else
        <table class="data">
            <thead>
            <tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Juridiction</th><th>État</th><th>Dernier accès</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($utilisateurs as $compte)
                <tr>
                    <td><strong>{{ $compte->name }}</strong>
                        @if($compte->fonction)<br><span class="muted">{{ $compte->fonction }}</span>@endif</td>
                    <td class="muted">{{ $compte->email }}</td>
                    <td><span class="badge badge--{{ $compte->role()->value === 'ADMIN' ? 'dark' : ($compte->role()->value === 'SUPPORT' ? 'purple' : 'info') }}">{{ $compte->libelleRole() }}</span></td>
                    <td>{{ $compte->juridiction?->libelle ?? '—' }}</td>
                    <td>
                        @if($compte->actif)
                            <span class="badge badge--success">Actif</span>
                        @else
                            <span class="badge badge--neutral">Désactivé</span>
                        @endif
                    </td>
                    <td class="muted">{{ $compte->dernier_acces_le?->format('d/m/Y H:i') ?? 'Jamais' }}</td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('utilisateurs.edit', $compte) }}" class="btn btn--ghost btn--small">Modifier</a>
                        @if($compte->id !== auth()->id())
                            <form method="POST" action="{{ route('utilisateurs.basculer', $compte) }}" class="inline-form">
                                @csrf
                                <button type="submit" class="btn btn--ghost btn--small">{{ $compte->actif ? 'Désactiver' : 'Activer' }}</button>
                            </form>
                            <form method="POST" action="{{ route('utilisateurs.destroy', $compte) }}" class="inline-form"
                                  onsubmit="return confirm('Supprimer le compte {{ $compte->email }} ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn--link" style="color:var(--danger);">Supprimer</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $utilisateurs->links() }}
    @endif
</div>
@endsection
