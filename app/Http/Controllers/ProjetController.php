<?php
namespace App\Http\Controllers;

use App\Models\Projet;
use App\Models\Membre;
use App\Models\Mandat;
use Illuminate\Http\Request;

class ProjetController extends Controller
{
    public function index()
    {
        $projets = Projet::with('responsable')->latest()->paginate(12);
        return view('projets.index', compact('projets'));
    }

    public function create()
    {
        return view('projets.create', ['projet' => new Projet(), 'membres' => Membre::orderBy('nom')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->valide($request);
        $data['mandat_id'] = Mandat::actif()?->id;
        $projet = Projet::create($data);
        return redirect()->route('projets.show', $projet)->with('ok', 'Projet créé.');
    }

    public function show(Projet $projet)
    {
        $projet->load('responsable', 'contributions', 'depenses');
        return view('projets.show', compact('projet'));
    }

    public function edit(Projet $projet)
    {
        return view('projets.edit', ['projet' => $projet, 'membres' => Membre::orderBy('nom')->get()]);
    }

    public function update(Request $request, Projet $projet)
    {
        $projet->update($this->valide($request));
        return redirect()->route('projets.show', $projet)->with('ok', 'Projet mis à jour.');
    }

    public function destroy(Projet $projet)
    {
        $projet->delete();
        return redirect()->route('projets.index')->with('ok', 'Projet supprimé.');
    }

    private function valide(Request $request): array
    {
        return $request->validate([
            'titre'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'statut'         => ['required', 'in:a_venir,en_cours,termine'],
            'avancement'     => ['required', 'integer', 'min:0', 'max:100'],
            'public'         => ['nullable', 'boolean'],
            'responsable_id' => ['nullable', 'exists:membres,id'],
        ]) + ['public' => $request->boolean('public')];
    }
}
