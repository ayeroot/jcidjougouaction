<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Partenaire;
use App\Models\Projet;
use App\Models\Mandat;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        $mandat     = Mandat::actif();
        $projets    = Projet::publics()->latest()->take(6)->get();
        $partenaires = Partenaire::publics()->get();

        // Contenu éditable du site vitrine (réglages administrables).
        $vitrine = Setting::tousLesReglages();

        return view('public.home', compact('mandat', 'projets', 'partenaires', 'vitrine'));
    }
}
