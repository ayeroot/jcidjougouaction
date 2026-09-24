<?php
namespace App\Http\Controllers;

use App\Models\Partenaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartenaireController extends Controller
{
    public function index()
    {
        $partenaires = Partenaire::withCount('contributions')->orderBy('nom')->paginate(15);
        return view('partenaires.index', compact('partenaires'));
    }

    public function store(Request $request)
    {
        $data = $this->gererLogo($request, $this->valide($request), null);
        Partenaire::create($data);
        return back()->with('ok', 'Partenaire ajouté.');
    }

    public function edit(Partenaire $partenaire)
    {
        return view('partenaires.edit', compact('partenaire'));
    }

    public function update(Request $request, Partenaire $partenaire)
    {
        $data = $this->gererLogo($request, $this->valide($request), $partenaire);
        $partenaire->update($data);
        return redirect()->route('partenaires.index')->with('ok', 'Partenaire mis à jour.');
    }

    public function destroy(Partenaire $partenaire)
    {
        if ($partenaire->logo) {
            Storage::disk('public')->delete($partenaire->logo);
        }
        $partenaire->delete();
        return back()->with('ok', 'Partenaire supprimé.');
    }

    /** Stocke le logo téléversé (disque public) et remplace l'ancien. */
    private function gererLogo(Request $request, array $data, ?Partenaire $partenaire): array
    {
        if ($request->hasFile('logo')) {
            if ($partenaire && $partenaire->logo) {
                Storage::disk('public')->delete($partenaire->logo);
            }
            $data['logo'] = $request->file('logo')->store('partenaires', 'public');
        } else {
            unset($data['logo']); // ne pas écraser le logo existant avec null
        }
        return $data;
    }

    private function valide(Request $request): array
    {
        return $request->validate([
            'nom'         => ['required', 'string', 'max:255'],
            'type'        => ['nullable', 'string', 'max:255'],
            'contact'     => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo'        => ['nullable', 'image', 'max:2048'],
            'public'      => ['nullable', 'boolean'],
        ]) + ['public' => $request->boolean('public')];
    }
}