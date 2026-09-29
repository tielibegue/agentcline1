<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint une route aux rôles indiqués (ex: role:ADMIN,SUPPORT).
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $utilisateur = $request->user();

        if (! $utilisateur) {
            return redirect()->route('connexion');
        }

        // Un compte désactivé est immédiatement déconnecté.
        if (! $utilisateur->actif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('connexion')
                ->withErrors(['email' => 'Votre compte a été désactivé. Contactez l’administrateur.']);
        }

        if ($roles !== [] && ! in_array($utilisateur->role()->value, $roles, true)) {
            abort(403, "Vous n'avez pas le rôle requis pour accéder à cette page.");
        }

        return $next($request);
    }
}
