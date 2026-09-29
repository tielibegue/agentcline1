<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'Support') — Support Agent-Justice</title>
    <link rel="stylesheet" href="{{ asset('css/support.css') }}">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚖️</text></svg>">
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <div class="logo">
                <div class="sigle">AJ</div>
                <div>
                    <h1>Support<br>Agent-Justice</h1>
                    <p>Plaintes &amp; bugs des juridictions</p>
                </div>
            </div>
        </div>

        <nav class="nav">
            <div class="section">Pilotage</div>
            <a class="item {{ request()->routeIs('tableau-de-bord') ? 'active' : '' }}" href="{{ route('tableau-de-bord') }}">
                <span class="icon">📊</span> Tableau de bord
            </a>

            <div class="section">Demandes</div>
            <a class="item {{ request()->routeIs('demandes.index', 'demandes.show', 'demandes.edit') ? 'active' : '' }}" href="{{ route('demandes.index') }}">
                <span class="icon">📥</span> Toutes les demandes
            </a>
            <a class="item {{ request()->routeIs('demandes.create') ? 'active' : '' }}" href="{{ route('demandes.create') }}">
                <span class="icon">➕</span> Nouvelle demande
            </a>
            @if(auth()->user()?->estInterne())
                <a class="item" href="{{ route('demandes.index', ['ouverts_seulement' => 1, 'non_affectes' => 1]) }}">
                    <span class="icon">🔔</span> À affecter
                    @if(($aAffecter ?? 0) > 0)
                        <span class="count">{{ $aAffecter }}</span>
                    @endif
                </a>
                <a class="item" href="{{ route('demandes.index', ['ouverts_seulement' => 1]) }}">
                    <span class="icon">⏳</span> En cours
                </a>
            @endif

            @if(auth()->user()?->estAdministrateur())
                <div class="section">Administration</div>
                <a class="item {{ request()->routeIs('juridictions.*') ? 'active' : '' }}" href="{{ route('juridictions.index') }}">
                    <span class="icon">🏛️</span> Juridictions
                </a>
                <a class="item {{ request()->routeIs('utilisateurs.*') ? 'active' : '' }}" href="{{ route('utilisateurs.index') }}">
                    <span class="icon">👥</span> Comptes
                </a>
            @endif
        </nav>

        <div class="userbox">
            <div class="who">
                <div class="avatar">{{ auth()->user()->initiales() }}</div>
                <div>
                    <div class="name">{{ auth()->user()->name }}</div>
                    <div class="role">{{ auth()->user()->libelleRole() }}
                        @if(auth()->user()->juridiction)
                            · {{ auth()->user()->juridiction->libelle }}
                        @endif
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('deconnexion') }}">
                @csrf
                <button type="submit">Se déconnecter</button>
            </form>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <h2>@yield('titre', 'Support')</h2>
            <div class="spacer"></div>
            @if(auth()->user()->juridiction)
                <div class="juridiction">🏛️ {{ auth()->user()->juridiction->nomComplet() }}</div>
            @endif
        </header>

        <main class="content">
            @if(session('succes'))
                <div class="alert alert--success">{{ session('succes') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert--danger">
                    <strong>Veuillez corriger les erreurs suivantes :</strong>
                    <ul>
                        @foreach($errors->all() as $erreur)
                            <li>{{ $erreur }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('contenu')
        </main>
    </div>
</div>
</body>
</html>
