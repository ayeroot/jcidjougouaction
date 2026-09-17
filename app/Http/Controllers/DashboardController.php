<?php
namespace App\Http\Controllers;

use App\Models\Membre;
use App\Models\Postulant;
use App\Models\Formation;
use App\Models\Projet;
use App\Models\Cotisation;
use App\Models\Contribution;
use App\Models\Depense;
use App\Models\Mandat;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'membres'           => Membre::count(),
            'postulants_actifs' => Postulant::whereIn('statut', ['nouveau', 'contacte', 'en_formation'])->count(),
            'formations_mois'   => Formation::whereYear('date_formation', now()->year)
                                            ->whereMonth('date_formation', now()->month)->count(),
            'projets_en_cours'  => Projet::where('statut', 'en_cours')->count(),
        ];

        // Solde de caisse (réservé aux rôles financiers, calculé ici pour l'affichage conditionnel).
        $solde = Cotisation::sum('montant') + Contribution::sum('montant') - Depense::sum('montant');

        $mandat = Mandat::actif();

        // Anniversaires du mois en cours
        $anniversaires = Membre::anniversaireMois(now()->month)->get()
                               ->sortBy(fn ($m) => $m->date_naissance->day)
                               ->values();

        return view('dashboard', compact('stats', 'solde', 'mandat', 'anniversaires'));
    }
}
