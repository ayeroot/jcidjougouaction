<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;

class HistoriqueController extends Controller
{
    /** Correspondance nom de classe -> libellé lisible. */
    public const ENTITES = [
        'App\\Models\\Membre'     => 'Membre',
        'App\\Models\\Postulant'  => 'Postulant',
    ];

    public function index()
    {
        $logs = AuditLog::with('user')->latest()->paginate(30);
        return view('historique.index', compact('logs'));
    }
}
