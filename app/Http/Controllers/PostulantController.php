<?php
namespace App\Http\Controllers;

use App\Models\Postulant;
use App\Models\Membre;
use Illuminate\Http\Request;

class PostulantController extends Controller
{
    public function index(Request $request)
    {
        $query = Postulant::query();
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        $postulants = $query->latest()->paginate(15)->withQueryString();
        return view('postulants.index', compact('postulants'));
    }

    public function show(Postulant $postulant)
    {
        $postulant->load('formations');
        return view('postulants.show', compact('postulant'));
    }

    /** Mini-relevé imprimable des formations suivies par le postulant. */
    public function releve(Postulant $postulant)
    {
        $postulant->load('formations.formateur');
        return view('postulants.releve', compact('postulant'));
    }

    /** Avancement dans le pipeline. */
    public function updateStatut(Request $request, Postulant $postulant)
    {
        $data = $request->validate([
            'statut' => ['required', 'in:' . implode(',', Postulant::STATUTS)],
        ]);
        $postulant->update($data);
        return back()->with('ok', 'Statut mis à jour.');
    }

    /** Intégration : le postulant devient membre après examen réussi, avec une promotion. */
    public function convertir(Request $request, Postulant $postulant)
    {
        if ($postulant->membre_id) {
            return back()->with('ok', 'Ce postulant est déjà membre.');
        }

        $data = $request->validate([
            'promotion' => ['required', 'string', 'max:255'],
        ]);

        $membre = Membre::create([
            'nom'            => $postulant->nom,
            'prenom'         => $postulant->prenom,
            'date_naissance' => $postulant->date_naissance,
            'sexe'           => $postulant->sexe,
            'email'          => $postulant->email,
            'telephone'      => $postulant->telephone,
            'ville'          => $postulant->ville,
            'fonction'       => 'Membre',
            'promotion'      => $data['promotion'],
            'statut'         => 'actif',
            'date_adhesion'  => now(),
        ]);

        $postulant->update(['statut' => 'admis', 'membre_id' => $membre->id]);

        return redirect()->route('membres.show', $membre)
                         ->with('ok', 'Postulant intégré comme membre (promotion ' . $data['promotion'] . ').');
    }
}
