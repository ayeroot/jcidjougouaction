<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Postulant;
use Illuminate\Http\Request;

class InscriptionController extends Controller
{
    public function create()
    {
        return view('public.inscription');
    }

    public function store(Request $request)
    {
        // M4 — Pot de miel : un robot remplit le champ caché. On fait comme si tout allait
        // bien (pour ne pas lui apprendre à contourner), mais rien n'est enregistré.
        if ($request->filled('site_web')) {
            return redirect()->route('inscription.merci');
        }

        $data = $request->validate([
            'nom'            => ['required', 'string', 'max:255'],
            'prenom'         => ['required', 'string', 'max:255'],
            'date_naissance' => ['nullable', 'date'],
            'sexe'           => ['nullable', 'in:M,F'],
            'email'          => ['nullable', 'email', 'max:255'],
            'telephone'      => ['required', 'string', 'max:50'],
            'ville'          => ['nullable', 'string', 'max:255'],
            'motivation'     => ['nullable', 'string', 'max:2000'],
            'consentement'   => ['accepted'],
        ], [
            'consentement.accepted' => 'Merci d\'accepter le traitement de vos données pour envoyer votre candidature.',
        ]);

        // Preuve du consentement (date), sans conserver le champ brut.
        unset($data['consentement']);
        $data['consentement_at'] = now();

        // Le postulant entre dans le pipeline au statut « nouveau ».
        $data['statut'] = 'nouveau';
        Postulant::create($data);

        return redirect()->route('inscription.merci');
    }

    public function merci()
    {
        return view('public.merci');
    }
}
