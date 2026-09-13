<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Partenaire;
use App\Models\Projet;
use App\Models\Mandat;

class HomeController extends Controller
{
    public function index()
    {
        $mandat     = Mandat::actif();
        $projets    = Projet::publics()->latest()->take(6)->get();
        $partenaires = Partenaire::publics()->get();

        return view('public.home', compact('mandat', 'projets', 'partenaires'));
    }
}
