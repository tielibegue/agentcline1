@extends('layouts.invite')

@section('titre', 'Connexion')

@section('contenu')
<div class="login-wrap">
    <div class="login-card">
        <div class="sigle">AJ</div>
        <h1>Support Agent-Justice</h1>
        <p class="subtitle">Déclarez vos plaintes et bugs, suivez leur résolution.</p>

        @if(session('succes'))
            <div class="alert alert--success">{{ session('succes') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert--danger">
                <ul>
                    @foreach($errors->all() as $erreur)
                        <li>{{ $erreur }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('connexion.soumettre') }}">
            @csrf

            <div class="form-grid" style="grid-template-columns: 1fr;">
                <div class="field">
                    <label for="email">Adresse e-mail <span class="req">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                </div>

                <div class="field">
                    <label for="password">Mot de passe <span class="req">*</span></label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>

                <label class="checkline">
                    <input type="checkbox" name="remember" value="1"> Rester connecté sur ce poste
                </label>

                <div class="field">
                    <button type="submit" class="btn btn--primary" style="justify-content:center;">Se connecter</button>
                </div>
            </div>
        </form>

        <p class="foot">Accès réservé aux juridictions et à l’équipe support.<br>Un problème de connexion ? Contactez l’administrateur.</p>
    </div>
</div>
@endsection
