<?php
namespace App\Http\Controllers;

use App\Models\AffectationCdl;
use App\Models\Mandat;
use App\Models\Membre;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CdlController extends Controller
{
    public function __construct(private AccountService $comptes) {}

    /**
     * Affecte un membre à un poste du CDL pour un mandat.
     * Règles (appliquées côté backend) :
     *  - un seul membre par poste et par mandat (contrainte d'unicité en base) ;
     *  - le membre reçoit automatiquement un compte utilisateur (si absent) avec le rôle du poste ;
     *  - l'ancien titulaire du poste est automatiquement désactivé.
     */
    public function affecter(Request $request, Mandat $mandat)
    {
        $data = $request->validate([
            'poste'     => ['required', Rule::in(AffectationCdl::postes())],
            'membre_id' => ['required', 'exists:membres,id'],
        ]);

        $membre = Membre::findOrFail($data['membre_id']);
        $role   = AffectationCdl::POSTE_ROLE[$data['poste']];

        // Un membre ne peut occuper deux postes différents dans le même mandat.
        $dejaAutrePoste = AffectationCdl::where('mandat_id', $mandat->id)
            ->where('membre_id', $membre->id)
            ->where('poste', '!=', $data['poste'])->exists();
        if ($dejaAutrePoste) {
            throw ValidationException::withMessages([
                'membre_id' => "Ce membre occupe déjà un autre poste dans ce mandat.",
            ]);
        }

        // Affectation : remplace le titulaire éventuel du poste (unicité mandat+poste).
        $affectation = AffectationCdl::updateOrCreate(
            ['mandat_id' => $mandat->id, 'poste' => $data['poste']],
            ['membre_id' => $membre->id]
        );

        // La fonction du membre reflète son poste au bureau.
        $membre->update(['fonction' => $data['poste']]);

        // Création automatique du compte (si besoin) + rôle + désactivation de l'ancien.
        $res = $this->comptes->creerPourMembre($membre, $role);

        $msg = "Poste « {$data['poste']} » affecté à {$membre->nom_complet}.";
        if ($res['user']) {
            $msg .= $res['email_envoye']
                ? " Un email d'activation lui a été envoyé."
                : " (compte créé, mais email d'activation non envoyé — vérifier SMTP).";
        } else {
            $msg .= ' ' . $res['message'];
        }

        return back()->with('ok', $msg);
    }

    /** Retire une affectation (le compte est conservé mais peut être désactivé par l'admin). */
    public function retirer(Mandat $mandat, AffectationCdl $affectation)
    {
        abort_unless($affectation->mandat_id === $mandat->id, 404);
        $affectation->delete();
        return back()->with('ok', 'Affectation retirée.');
    }
}
