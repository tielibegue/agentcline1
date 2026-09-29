<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Http\Requests\StoreUtilisateurRequest;
use App\Http\Requests\UpdateUtilisateurRequest;
use App\Models\Juridiction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UtilisateurController extends Controller
{
    public function index(Request $requete)
    {
        $this->authorize('viewAny', User::class);

        $recherche = $requete->string('recherche')->toString();
        $role = $requete->string('role')->toString();

        $utilisateurs = User::query()
            ->with('juridiction')
            ->when($recherche, fn ($q) => $q->where(function ($sousRequete) use ($recherche): void {
                $terme = '%'.$recherche.'%';

                $sousRequete->where('name', 'like', $terme)
                    ->orWhere('email', 'like', $terme);
            }))
            ->when($role, fn ($q) => $q->where('role', $role))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('utilisateurs.index', [
            'utilisateurs' => $utilisateurs,
            'recherche' => $recherche,
            'role' => $role,
            'roles' => RoleUtilisateur::options(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('utilisateurs.create', $this->donneesFormulaire());
    }

    public function store(StoreUtilisateurRequest $requete): RedirectResponse
    {
        $donnees = $requete->validated();
        $donnees['actif'] = $requete->boolean('actif', true);

        if ($donnees['role'] !== RoleUtilisateur::JURIDICTION->value) {
            $donnees['juridiction_id'] = null;
        }

        $utilisateur = User::query()->create($donnees);

        return redirect()
            ->route('utilisateurs.index')
            ->with('succes', 'Compte '.$utilisateur->email.' créé ('.$utilisateur->libelleRole().').');
    }

    public function edit(User $utilisateur)
    {
        $this->authorize('update', $utilisateur);

        return view('utilisateurs.edit', array_merge(
            ['utilisateur' => $utilisateur],
            $this->donneesFormulaire(),
        ));
    }

    public function update(UpdateUtilisateurRequest $requete, User $utilisateur): RedirectResponse
    {
        $donnees = $requete->validated();
        $donnees['actif'] = $requete->boolean('actif');

        // Mot de passe inchangé s'il n'est pas renseigné.
        if (empty($donnees['password'])) {
            unset($donnees['password']);
        }

        if (($donnees['role'] ?? null) !== RoleUtilisateur::JURIDICTION->value) {
            $donnees['juridiction_id'] = null;
        }

        $utilisateur->fill($donnees);
        $utilisateur->save();

        return redirect()
            ->route('utilisateurs.index')
            ->with('succes', 'Compte '.$utilisateur->email.' mis à jour.');
    }

    public function destroy(Request $requete, User $utilisateur): RedirectResponse
    {
        $this->authorize('delete', $utilisateur);

        $email = $utilisateur->email;
        $utilisateur->delete();

        return redirect()
            ->route('utilisateurs.index')
            ->with('succes', 'Compte '.$email.' supprimé.');
    }

    /**
     * Active ou désactive un compte (hors compte courant).
     */
    public function basculer(Request $requete, User $utilisateur): RedirectResponse
    {
        $this->authorize('update', $utilisateur);

        abort_if($utilisateur->id === $requete->user()->id, 422, 'Vous ne pouvez pas désactiver votre propre compte.');

        $utilisateur->forceFill(['actif' => ! $utilisateur->actif])->save();

        return back()->with('succes', 'Compte '.$utilisateur->email.($utilisateur->actif ? ' activé.' : ' désactivé.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function donneesFormulaire(): array
    {
        return [
            'roles' => RoleUtilisateur::options(),
            'juridictions' => Juridiction::query()->where('actif', true)->orderBy('libelle')->get(),
        ];
    }
}
