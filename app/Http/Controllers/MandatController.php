<?php
namespace App\Http\Controllers;

use App\Models\Mandat;
use App\Models\Membre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MandatController extends Controller
{
    public function index()
    {
        $mandats = Mandat::orderByDesc('annee')->get();
        return view('mandats.index', compact('mandats'));
    }

    public function show(Mandat $mandat)
    {
        // Membres du CDL = membres portant une fonction du bureau (hors « Membre »).
        $cdl = Membre::where('fonction', '!=', 'Membre')->whereNotNull('fonction')->orderBy('nom')->get();
        return view('mandats.show', compact('mandat', 'cdl'));
    }

    public function create()
    {
        return view('mandats.create', ['mandat' => new Mandat()]);
    }

    public function store(Request $request)
    {
        $data = $this->valide($request);
        $data = $this->gererFichiers($request, $data);
        $mandat = Mandat::create($data);
        return redirect()->route('mandats.show', $mandat)->with('ok', 'Mandat créé.');
    }

    public function edit(Mandat $mandat)
    {
        return view('mandats.edit', compact('mandat'));
    }

    public function update(Request $request, Mandat $mandat)
    {
        $data = $this->valide($request);
        $data = $this->gererFichiers($request, $data, $mandat);
        $mandat->update($data);
        return redirect()->route('mandats.show', $mandat)->with('ok', 'Configuration du mandat mise à jour.');
    }

    /** Définir le mandat actif (un seul à la fois). */
    public function setActif(Mandat $mandat)
    {
        Mandat::query()->update(['actif' => false]);
        $mandat->update(['actif' => true]);
        return back()->with('ok', "Mandat {$mandat->annee} défini comme actif.");
    }

    private function valide(Request $request): array
    {
        return $request->validate([
            'annee'         => ['required', 'string', 'max:20'],
            'theme'         => ['nullable', 'string', 'max:255'],
            'couleur'       => ['nullable', 'string', 'max:20'],
            'date_debut'    => ['nullable', 'date'],
            'date_fin'      => ['nullable', 'date'],
            'logo'          => ['nullable', 'image', 'max:2048'],
            'photo_famille' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function gererFichiers(Request $request, array $data, ?Mandat $mandat = null): array
    {
        foreach (['logo', 'photo_famille'] as $champ) {
            if ($request->hasFile($champ)) {
                if ($mandat && $mandat->$champ) {
                    Storage::disk('public')->delete($mandat->$champ);
                }
                $data[$champ] = $request->file($champ)->store('mandats', 'public');
            } else {
                unset($data[$champ]);
            }
        }
        return $data;
    }
}
