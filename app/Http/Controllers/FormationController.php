<?php
namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Formateur;
use App\Models\Postulant;
use App\Models\Mandat;
use App\Models\RapportFormation;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    /** Statuts de postulant éligibles à une formation (tous sauf rejetés). */
    private function postulantsEligibles()
    {
        return Postulant::whereIn('statut', ['nouveau', 'contacte', 'en_formation', 'admis'])
                        ->orderBy('nom')->get();
    }

    public function index(Request $request)
    {
        $query = Formation::with('formateur')->orderByDesc('date_formation');

        // Filtre "formations du mois" : paramètre mois = YYYY-MM
        $mois = $request->input('mois');
        if ($mois && preg_match('/^\d{4}-\d{2}$/', $mois)) {
            [$y, $m] = explode('-', $mois);
            $query->duMois((int) $y, (int) $m);
        }

        $formations = $query->paginate(15)->withQueryString();
        return view('formations.index', compact('formations', 'mois'));
    }

    public function create()
    {
        return view('formations.create', [
            'formation'  => new Formation(),
            'formateurs' => Formateur::orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->valide($request);
        $data['formateur_id'] = $this->resoudreFormateur($request);
        $data['mandat_id'] = Mandat::actif()?->id;

        $formation = Formation::create($data);
        return redirect()->route('formations.show', $formation)->with('ok', 'Formation planifiée.');
    }

    public function show(Formation $formation)
    {
        $formation->load('formateur', 'rapport', 'postulants');
        $eligibles = $this->postulantsEligibles();
        // Map postulant_id => present (pour cocher la liste)
        $presences = $formation->postulants->pluck('pivot.present', 'id');

        return view('formations.show', compact('formation', 'eligibles', 'presences'));
    }

    public function edit(Formation $formation)
    {
        return view('formations.edit', [
            'formation'  => $formation,
            'formateurs' => Formateur::orderBy('nom')->get(),
        ]);
    }

    public function update(Request $request, Formation $formation)
    {
        $data = $this->valide($request);
        $data['formateur_id'] = $this->resoudreFormateur($request);
        $formation->update($data);
        return redirect()->route('formations.show', $formation)->with('ok', 'Formation mise à jour.');
    }

    public function destroy(Formation $formation)
    {
        $formation->delete();
        return redirect()->route('formations.index')->with('ok', 'Formation supprimée.');
    }

    /** Pointage : enregistre les présents de la formation. */
    public function pointage(Request $request, Formation $formation)
    {
        $presents = collect($request->input('present', []))->map(fn ($v) => (int) $v);
        $sync = [];
        foreach ($this->postulantsEligibles() as $p) {
            $sync[$p->id] = ['present' => $presents->contains($p->id)];
        }
        $formation->postulants()->sync($sync);
        return back()->with('ok', 'Présences enregistrées.');
    }

    /** Rédaction / mise à jour du rapport de formation. */
    public function rapport(Request $request, Formation $formation)
    {
        $data = $request->validate(['contenu' => ['required', 'string']]);
        RapportFormation::updateOrCreate(
            ['formation_id' => $formation->id],
            ['contenu' => $data['contenu'], 'redige_par' => $request->user()->id]
        );
        return back()->with('ok', 'Rapport enregistré.');
    }

    /** Liste de présence imprimable. */
    public function listePresence(Formation $formation)
    {
        $formation->load('formateur');
        $eligibles = $this->postulantsEligibles();
        return view('formations.liste_presence', compact('formation', 'eligibles'));
    }

    private function valide(Request $request): array
    {
        return $request->validate([
            'titre'          => ['required', 'string', 'max:255'],
            'theme'          => ['nullable', 'string', 'max:255'],
            'objectifs'      => ['nullable', 'string'],
            'date_formation' => ['nullable', 'date'],
            'lieu'           => ['nullable', 'string', 'max:255'],
            'statut'         => ['required', 'in:planifiee,realisee,annulee'],
        ]);
    }

    /** Formateur existant sélectionné, ou nouveau formateur saisi. */
    private function resoudreFormateur(Request $request): ?int
    {
        if ($request->filled('nouveau_formateur')) {
            $f = Formateur::create([
                'nom'  => $request->input('nouveau_formateur'),
                'type' => $request->input('nouveau_formateur_type', 'externe'),
            ]);
            return $f->id;
        }
        return $request->input('formateur_id') ?: null;
    }
}
