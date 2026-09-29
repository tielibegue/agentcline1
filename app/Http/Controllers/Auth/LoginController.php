<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('tableau-de-bord');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $requete): RedirectResponse
    {
        $requete->authenticate();
        $requete->session()->regenerate();

        $requete->user()->forceFill(['dernier_acces_le' => now()])->save();

        return redirect()
            ->intended(route('tableau-de-bord'))
            ->with('succes', 'Bienvenue '.$requete->user()->name.' !');
    }

    public function logout(Request $requete): RedirectResponse
    {
        Auth::logout();

        $requete->session()->invalidate();
        $requete->session()->regenerateToken();

        return redirect()->route('connexion')->with('succes', 'Vous êtes bien déconnecté.');
    }
}
