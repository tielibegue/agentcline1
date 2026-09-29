<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JuridictionController;
use App\Http\Controllers\TicketCommentaireController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketPieceJointeController;
use App\Http\Controllers\UtilisateurController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application de support Agent-Justice
| Les juridictions déclarent leurs plaintes et bugs, le support les résout.
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/tableau-de-bord');

// ---------------------------------------------------------------------
// Authentification
// ---------------------------------------------------------------------

Route::middleware('guest')->group(function (): void {
    Route::get('/connexion', [LoginController::class, 'showLoginForm'])->name('connexion');
    Route::post('/connexion', [LoginController::class, 'login'])->name('connexion.soumettre');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/deconnexion', [LoginController::class, 'logout'])->name('deconnexion');

    // -----------------------------------------------------------------
    // Tableau de bord
    // -----------------------------------------------------------------
    Route::get('/tableau-de-bord', [DashboardController::class, 'index'])->name('tableau-de-bord');

    // -----------------------------------------------------------------
    // Demandes (plaintes et bugs)
    // -----------------------------------------------------------------
    Route::get('/demandes/export', [TicketController::class, 'export'])->name('demandes.export');

    Route::resource('demandes', TicketController::class)->parameters([
        'demandes' => 'ticket',
    ]);

    Route::post('/demandes/{ticket}/affecter', [TicketController::class, 'affecter'])->name('demandes.affecter');
    Route::post('/demandes/{ticket}/statut', [TicketController::class, 'changerStatut'])->name('demandes.statut');
    Route::post('/demandes/{ticket}/resoudre', [TicketController::class, 'resoudre'])->name('demandes.resoudre');
    Route::post('/demandes/{ticket}/rejeter', [TicketController::class, 'rejeter'])->name('demandes.rejeter');
    Route::post('/demandes/{ticket}/cloturer', [TicketController::class, 'cloturer'])->name('demandes.cloturer');

    Route::post('/demandes/{ticket}/commentaires', [TicketCommentaireController::class, 'store'])
        ->name('demandes.commentaires.store');

    Route::post('/demandes/{ticket}/pieces-jointes', [TicketPieceJointeController::class, 'store'])
        ->name('demandes.pieces.store');
    Route::get('/demandes/{ticket}/pieces-jointes/{pieceJointe}/telecharger', [TicketPieceJointeController::class, 'download'])
        ->name('demandes.pieces.download');
    Route::delete('/demandes/{ticket}/pieces-jointes/{pieceJointe}', [TicketPieceJointeController::class, 'destroy'])
        ->name('demandes.pieces.destroy');

    // -----------------------------------------------------------------
    // Administration (référentiels)
    // -----------------------------------------------------------------
    Route::middleware('role:ADMIN')->group(function (): void {
        Route::resource('juridictions', JuridictionController::class)->except(['show']);
        Route::resource('utilisateurs', UtilisateurController::class)
            ->parameters(['utilisateurs' => 'utilisateur'])
            ->except(['show']);
        Route::post('/utilisateurs/{utilisateur}/basculer', [UtilisateurController::class, 'basculer'])
            ->name('utilisateurs.basculer');
    });
});
