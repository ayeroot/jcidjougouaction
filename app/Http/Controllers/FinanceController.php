<?php
namespace App\Http\Controllers;

use App\Models\Cotisation;
use App\Models\Contribution;
use App\Models\Depense;
use App\Models\Membre;
use App\Models\Mandat;
use App\Models\Projet;
use App\Models\Partenaire;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    /** Vue d'ensemble : solde, totaux, budget par projet, membres honorables. */
    public function index()
    {
        $totalCotisations   = (float) Cotisation::sum('montant');
        $totalContributions = (float) Contribution::sum('montant');
        $totalDepenses      = (float) Depense::sum('montant');
        $solde = $totalCotisations + $totalContributions - $totalDepenses;

        // Budget par projet : recettes (contributions) - dépenses
        $projets = Projet::withSum('contributions as recettes', 'montant')
                         ->get()
                         ->map(function ($p) {
                             $p->depenses_total = (float) Depense::where('projet_id', $p->id)->sum('montant');
                             $p->recettes = (float) ($p->recettes ?? 0);
                             return $p;
                         });

        $mandat = Mandat::actif();
        $membres = Membre::orderBy('nom')->get();
        $honorables = $membres->filter->estHonorable()->count();
        $nonHonorables = $membres->count() - $honorables;

        return view('finances.index', compact(
            'totalCotisations', 'totalContributions', 'totalDepenses', 'solde',
            'projets', 'mandat', 'honorables', 'nonHonorables'
        ));
    }

    /* --------------------------------------------------- Cotisations */
    public function cotisations()
    {
        $cotisations = Cotisation::with('membre', 'mandat')->latest('date_cotisation')->paginate(15);
        $membres = Membre::orderBy('nom')->get();
        return view('finances.cotisations', compact('cotisations', 'membres'));
    }

    public function storeCotisation(Request $request)
    {
        $data = $request->validate([
            'membre_id'       => ['required', 'exists:membres,id'],
            'montant'         => ['required', 'numeric', 'min:0'],
            'date_cotisation' => ['required', 'date'],
        ]);
        $data['mandat_id'] = Mandat::actif()?->id;
        Cotisation::create($data);
        return back()->with('ok', 'Cotisation enregistrée.');
    }

    /* --------------------------------------------------- Contributions */
    public function contributions()
    {
        $contributions = Contribution::with('partenaire', 'projet')->latest('date_contribution')->paginate(15);
        $partenaires = Partenaire::orderBy('nom')->get();
        $projets = Projet::orderBy('titre')->get();
        return view('finances.contributions', compact('contributions', 'partenaires', 'projets'));
    }

    public function storeContribution(Request $request)
    {
        $data = $request->validate([
            'source'            => ['nullable', 'string', 'max:255'],
            'montant'           => ['required', 'numeric', 'min:0'],
            'date_contribution' => ['required', 'date'],
            'partenaire_id'     => ['nullable', 'exists:partenaires,id'],
            'projet_id'         => ['nullable', 'exists:projets,id'],
        ]);
        Contribution::create($data);
        return back()->with('ok', 'Contribution enregistrée.');
    }

    /* --------------------------------------------------- Dépenses */
    public function depenses()
    {
        $depenses = Depense::with('projet')->latest('date_depense')->paginate(15);
        $projets = Projet::orderBy('titre')->get();
        return view('finances.depenses', compact('depenses', 'projets'));
    }

    public function storeDepense(Request $request)
    {
        $data = $request->validate([
            'libelle'      => ['required', 'string', 'max:255'],
            'montant'      => ['required', 'numeric', 'min:0'],
            'date_depense' => ['required', 'date'],
            'projet_id'    => ['nullable', 'exists:projets,id'],
        ]);
        $data['mandat_id'] = Mandat::actif()?->id;
        Depense::create($data);
        return back()->with('ok', 'Dépense enregistrée.');
    }
}
