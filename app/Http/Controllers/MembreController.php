<?php
namespace App\Http\Controllers;

use App\Models\Membre;
use App\Models\Mandat;
use Illuminate\Http\Request;

class MembreController extends Controller
{
    /** Liste visible par tous les membres connectés, avec filtres. */
    public function index(Request $request)
    {
        $query = Membre::query()->with('carrieres');

        // Filtres
        if ($request->filled('recherche')) {
            $r = $request->recherche;
            $query->where(fn ($q) => $q->where('nom', 'like', "%$r%")
                                       ->orWhere('prenom', 'like', "%$r%"));
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->boolean('moins40')) {
            $query->moinsDe40();
        }
        if ($request->filled('carriere')) {
            $c = $request->carriere;
            $query->whereHas('carrieres', fn ($q) => $q->where('type', 'like', "%$c%"));
        }

        $membres = $query->orderBy('nom')->paginate(15)->withQueryString();
        $mandat  = Mandat::actif();

        return view('membres.index', compact('membres', 'mandat'));
    }

    public function show(Membre $membre)
    {
        $membre->load('carrieres', 'cotisations');
        return view('membres.show', compact('membre'));
    }

    public function create()
    {
        return view('membres.create', ['membre' => new Membre()]);
    }

    public function store(Request $request)
    {
        $membre = Membre::create($this->valide($request));
        return redirect()->route('membres.show', $membre)
                         ->with('ok', 'Membre créé avec succès.');
    }

    public function edit(Membre $membre)
    {
        return view('membres.edit', compact('membre'));
    }

    public function update(Request $request, Membre $membre)
    {
        $membre->update($this->valide($request));
        return redirect()->route('membres.show', $membre)
                         ->with('ok', 'Fiche mise à jour.');
    }

    public function destroy(Membre $membre)
    {
        $membre->delete();
        return redirect()->route('membres.index')->with('ok', 'Membre supprimé.');
    }

    private function valide(Request $request): array
    {
        return $request->validate([
            'nom'            => ['required', 'string', 'max:255'],
            'prenom'         => ['required', 'string', 'max:255'],
            'date_naissance' => ['nullable', 'date'],
            'sexe'           => ['nullable', 'in:M,F'],
            'email'          => ['nullable', 'email', 'max:255'],
            'telephone'      => ['nullable', 'string', 'max:50'],
            'fonction'       => ['nullable', \Illuminate\Validation\Rule::in(\App\Models\Membre::FONCTIONS)],
            'ville'          => ['nullable', 'string', 'max:255'],
            'adresse'        => ['nullable', 'string', 'max:255'],
            'statut'         => ['required', 'in:actif,honoraire,past_president,membre_honneur'],
            'date_adhesion'  => ['nullable', 'date'],
        ]);
    }
}
