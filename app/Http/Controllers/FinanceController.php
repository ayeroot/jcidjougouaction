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
        $attendu = Membre::COTISATION_ATTENDUE;
        return view('finances.cotisations', compact('cotisations', 'membres', 'attendu'));
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

    /* --------------------------------------------------- Recettes / Dons */
    public function contributions()
    {
        $contributions = Contribution::with('membre', 'partenaire', 'projet')->latest('date_contribution')->paginate(15);
        $membres = Membre::orderBy('nom')->get();
        $partenaires = Partenaire::orderBy('nom')->get();
        $projets = Projet::orderBy('titre')->get();
        $types = Contribution::TYPES;
        return view('finances.contributions', compact('contributions', 'membres', 'partenaires', 'projets', 'types'));
    }

    public function storeContribution(Request $request)
    {
        $data = $request->validate([
            'type'              => ['required', 'in:' . implode(',', array_keys(Contribution::TYPES))],
            'montant'           => ['required', 'numeric', 'min:0'],
            'date_contribution' => ['required', 'date'],
            'membre_id'         => ['nullable', 'exists:membres,id', 'required_if:type,participation_membre,contribution_membre'],
            'partenaire_id'     => ['nullable', 'exists:partenaires,id', 'required_if:type,don_partenaire'],
            'donateur'          => ['nullable', 'string', 'max:255', 'required_if:type,don_particulier'],
            'source'            => ['nullable', 'string', 'max:255'],
            'projet_id'         => ['nullable', 'exists:projets,id'],
        ]);
        Contribution::create($data);
        return back()->with('ok', 'Recette enregistrée.');
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
            'categorie'    => ['required', 'in:' . implode(',', array_keys(\App\Models\Depense::CATEGORIES))],
            'montant'      => ['required', 'numeric', 'min:0'],
            'date_depense' => ['required', 'date'],
            'projet_id'    => ['nullable', 'exists:projets,id', 'required_if:categorie,projet'],
        ]);
        // Une charge hors projet ne doit pas rester rattachée à un projet.
        if ($data['categorie'] !== 'projet') {
            $data['projet_id'] = null;
        }
        $data['mandat_id'] = Mandat::actif()?->id;
        Depense::create($data);
        return back()->with('ok', 'Dépense enregistrée.');
    }
};