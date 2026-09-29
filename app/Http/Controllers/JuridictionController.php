<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJuridictionRequest;
use App\Http\Requests\UpdateJuridictionRequest;
use App\Models\Juridiction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JuridictionController extends Controller
{
    public function index(Request $requete)
    {
        $this->authorize('viewAny', Juridiction::class);

        $recherche = $requete->string('recherche')->toString();

        $juridictions = Juridiction::query()
            ->withCount(['tickets', 'ticketsOuverts'])
            ->when($recherche, fn ($q) => $q->where(function ($sousRequete) use ($recherche): void {
                $terme = '%'.$recherche.'%';

                $sousRequete->where('libelle', 'like', $terme)
                    ->orWhere('code', 'like', $terme)
                    ->orWhere('ville', 'like', $terme);
            }))
            ->orderBy('libelle')
            ->paginate(15)
            ->withQueryString();

        return view('juridictions.index', [
            'juridictions' => $juridictions,
            'recherche' => $recherche,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Juridiction::class);

        return view('juridictions.create', [
            'types' => Juridiction::TYPES,
        ]);
    }

    public function store(StoreJuridictionRequest $requete): RedirectResponse
    {
        $donnees = $requete->validated();
        $donnees['actif'] = $requete->boolean('actif', true);

        $juridiction = Juridiction::query()->create($donnees);

        return redirect()
            ->route('juridictions.index')
            ->with('succes', 'Juridiction '.$juridiction->libelle.' créée.');
    }

    public function edit(Juridiction $juridiction)
    {
        $this->authorize('update', $juridiction);

        return view('juridictions.edit', [
            'juridiction' => $juridiction,
            'types' => Juridiction::TYPES,
        ]);
    }

    public function update(UpdateJuridictionRequest $requete, Juridiction $juridiction): RedirectResponse
    {
        $donnees = $requete->validated();
        $donnees['actif'] = $requete->boolean('actif');

        $juridiction->fill($donnees);
        $juridiction->save();

        return redirect()
            ->route('juridictions.index')
            ->with('succes', 'Juridiction '.$juridiction->libelle.' mise à jour.');
    }

    public function destroy(Request $requete, Juridiction $juridiction): RedirectResponse
    {
        $this->authorize('delete', $juridiction);

        if ($juridiction->ticketsOuverts()->exists()) {
            return back()->withErrors([
                'juridiction' => 'Impossible de supprimer '.$juridiction->libelle.' : des demandes y sont encore ouvertes.',
            ]);
        }

        $libelle = $juridiction->libelle;
        $juridiction->delete();

        return redirect()
            ->route('juridictions.index')
            ->with('succes', 'Juridiction '.$libelle.' supprimée.');
    }
}
