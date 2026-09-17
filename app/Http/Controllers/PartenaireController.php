<?php
namespace App\Http\Controllers;

use App\Models\Partenaire;
use Illuminate\Http\Request;

class PartenaireController extends Controller
{
    public function index()
    {
        $partenaires = Partenaire::withCount('contributions')->orderBy('nom')->paginate(15);
        return view('partenaires.index', compact('partenaires'));
    }

    public function store(Request $request)
    {
        Partenaire::create($this->valide($request));
        return back()->with('ok', 'Partenaire ajouté.');
    }

    public function edit(Partenaire $partenaire)
    {
        return view('partenaires.edit', compact('partenaire'));
    }

    public function update(Request $request, Partenaire $partenaire)
    {
        $partenaire->update($this->valide($request));
        return redirect()->route('partenaires.index')->with('ok', 'Partenaire mis à jour.');
    }

    public function destroy(Partenaire $partenaire)
    {
        $partenaire->delete();
        return back()->with('ok', 'Partenaire supprimé.');
    }

    private function valide(Request $request): array
    {
        return $request->validate([
            'nom'     => ['required', 'string', 'max:255'],
            'type'    => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'public'  => ['nullable', 'boolean'],
        ]) + ['public' => $request->boolean('public')];
    }
}
