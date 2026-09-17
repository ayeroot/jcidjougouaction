<?php
namespace App\Http\Controllers;

use App\Models\Membre;
use Illuminate\Http\Request;

class AnniversaireController extends Controller
{
    public function index(Request $request)
    {
        $mois = (int) $request->input('mois', now()->month);
        if ($mois < 1 || $mois > 12) $mois = now()->month;

        $membres = Membre::whereNotNull('date_naissance')
            ->whereMonth('date_naissance', $mois)
            ->get()
            ->sortBy(fn ($m) => (int) $m->date_naissance->format('d'))
            ->values();

        return view('anniversaires.index', compact('membres', 'mois'));
    }
}
