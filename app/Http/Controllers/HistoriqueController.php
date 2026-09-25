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
        'Spatie\\Permission\\Models\\Role' => 'Rôle',
        'Auth'                       => 'Connexion',
    ];

    /** Entités financières : visibles dans le journal uniquement avec « finances.voir ». */
    public const ENTITES_FINANCES = [
        'App\\Models\\Cotisation',
        'App\\Models\\Contribution',
        'App\\Models\\Depense',
    ];

    public function index()
    {
        $query = AuditLog::with('user')->latest();

        // M3 — Le journal ne doit pas contourner le cloisonnement des finances :
        // sans « finances.voir », les opérations financières sont masquées.
        if (! auth()->user()->can('finances.voir')) {
            $query->whereNotIn('auditable_type', self::ENTITES_FINANCES);
        }

        $logs = $query->paginate(40);
        return view('historique.index', compact('logs'));
    }
}
