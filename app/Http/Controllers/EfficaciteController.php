<?php
namespace App\Http\Controllers;

use App\Models\Standard;
use App\Models\PlanAction;
use App\Models\Mandat;
use Illuminate\Http\Request;

class EfficaciteController extends Controller
{
    public function index()
    {
        $standards = Standard::orderBy('categorie')->get();
        $total = $standards->count();
        $atteints = $standards->where('atteint', true)->count();
        $pourcentage = $total ? round($atteints / $total * 100) : 0;

        $actions = PlanAction::orderBy('mois')->orderBy('titre')->get();

        return view('efficacite.index', compact('standards', 'total', 'atteints', 'pourcentage', 'actions'));
    }

    public function storeStandard(Request $request)
    {
        $data = $request->validate([
            'libelle'   => ['required', 'string', 'max:255'],
            'categorie' => ['nullable', 'string', 'max:255'],
        ]);
        $data['mandat_id'] = Mandat::actif()?->id;
        Standard::create($data);
        return back()->with('ok', 'Standard ajouté.');
    }

    public function toggleStandard(Standard $standard)
    {
        $standard->update(['atteint' => ! $standard->atteint]);
        return back();
    }

    public function destroyStandard(Standard $standard)
    {
        $standard->delete();
        return back()->with('ok', 'Standard supprimé.');
    }

    public function storeAction(Request $request)
    {
        $data = $request->validate([
            'titre'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'mois'        => ['required', 'string'],
            'statut'      => ['required', 'in:' . implode(',', array_keys(PlanAction::STATUTS))],
        ]);
        $data['mandat_id'] = Mandat::actif()?->id;
        PlanAction::create($data);
        return back()->with('ok', 'Action ajoutée au plan.');
    }

    public function updateActionStatut(Request $request, PlanAction $action)
    {
        $data = $request->validate(['statut' => ['required', 'in:' . implode(',', array_keys(PlanAction::STATUTS))]]);
        $action->update($data);
        return back();
    }

    public function destroyAction(PlanAction $action)
    {
        $action->delete();
        return back()->with("ok", "Action supprimée.");
    }
}
