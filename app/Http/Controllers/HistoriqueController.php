<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;

class HistoriqueController extends Controller
{
    /** Correspondance nom de classe -> libellé lisible du module/entité. */
    public const ENTITES = [
        'App\\Models\\Membre'        => 'Membre',
        'App\\Models\\Postulant'     => 'Postulant',
        'App\\Models\\Formation'     => 'Formation',
        'App\\Models\\RapportFormation' => 'Rapport de formation',
        'App\\Models\\Formateur'     => 'Formateur',
        'App\\Models\\Projet'        => 'Projet',
        'App\\Models\\Partenaire'    => 'Partenaire',
        'App\\Models\\Cotisation'    => 'Cotisation',
        'App\\Models\\Contribution'  => 'Contribution',
        'App\\Models\\Depense'       => 'Dépense',
        'App\\Models\\Archive'       => 'Archive',
        'App\\Models\\Standard'      => 'Standard',
        'App\\Models\\PlanAction'    => "Plan d'action",
        'App\\Models\\Carriere'      => 'Carrière',
        'App\\Models\\Mandat'        => 'Mandat',
        'App\\Models\\User'          => 'Compte utilisateur',
        'App\\Models\\AffectationCdl'=> 'Affectation CDL',
    ];

    public function index()
    {
        $logs = AuditLog::with('user')->latest()->paginate(40);
        return view('historique.index', compact('logs'));
    }
}
