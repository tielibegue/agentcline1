<?php

namespace App\Providers;

use App\Models\Ticket;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pagination maison (pas de Tailwind : aucun build npm requis).
        Paginator::defaultView('pagination.simple');
        Paginator::defaultSimpleView('pagination.simple');

        // Compteur « à affecter » disponible dans la barre latérale.
        View::composer('layouts.app', function (ViewContract $vue): void {
            $utilisateur = auth()->user();

            $vue->with('aAffecter', $utilisateur && $utilisateur->estInterne()
                ? Ticket::query()->ouverts()->whereNull('assigne_a_id')->count()
                : 0);
        });
    }
}
