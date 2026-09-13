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
        $data = $request->validate([
            'nom'            => ['required', 'string', 'max:255'],
            'prenom'         => ['required', 'string', 'max:255'],
            'date_naissance' => ['nullable', 'date'],
            'sexe'           => ['nullable', 'in:M,F'],
            'email'          => ['nullable', 'email', 'max:255'],
            'telephone'      => ['required', 'string', 'max:50'],
            'ville'          => ['nullable', 'string', 'max:255'],
            'motivation'     => ['nullable', 'string', 'max:2000'],
        ]);

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
