<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VitrineController extends Controller
{
    /** Champs de contenu éditables du site vitrine (clé => libellé). */
    public const CHAMPS = [
        'vitrine_hero_titre'    => 'Titre principal (hero)',
        'vitrine_hero_sous_titre' => 'Sous-titre (hero)',
        'vitrine_mission'       => 'Texte de mission',
        'vitrine_contact_ville' => 'Contact — ville / adresse',
        'vitrine_contact_email' => 'Contact — email',
        'vitrine_contact_tel'   => 'Contact — téléphone',
    ];

    /** Sections activables/désactivables. */
    public const SECTIONS = [
        'vitrine_section_projets'     => 'Afficher la section « Projets »',
        'vitrine_section_partenaires' => 'Afficher la section « Partenaires »',
        'vitrine_section_inscription' => 'Afficher l\'appel à candidature',
    ];

    public function edit()
    {
        return view('admin.vitrine.edit', [
            'champs'   => self::CHAMPS,
            'sections' => self::SECTIONS,
            'valeurs'  => Setting::tousLesReglages(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'vitrine_hero_titre'      => ['nullable', 'string', 'max:255'],
            'vitrine_hero_sous_titre' => ['nullable', 'string', 'max:500'],
            'vitrine_mission'         => ['nullable', 'string', 'max:2000'],
            'vitrine_contact_ville'   => ['nullable', 'string', 'max:255'],
            'vitrine_contact_email'   => ['nullable', 'email', 'max:255'],
            'vitrine_contact_tel'     => ['nullable', 'string', 'max:50'],
            'vitrine_logo'            => ['nullable', 'image', 'max:2048'],
        ]);

        foreach (array_keys(self::CHAMPS) as $cle) {
            Setting::put($cle, $data[$cle] ?? null);
        }
        // Sections : cases cochées = "1", sinon "0".
        foreach (array_keys(self::SECTIONS) as $cle) {
            Setting::put($cle, $request->boolean($cle) ? '1' : '0');
        }
        // Logo de la vitrine (facultatif).
        if ($request->hasFile('vitrine_logo')) {
            $ancien = Setting::get('vitrine_logo');
            if ($ancien) Storage::disk('public')->delete($ancien);
            Setting::put('vitrine_logo', $request->file('vitrine_logo')->store('vitrine', 'public'));
        }

        return back()->with('ok', 'Contenu du site vitrine mis à jour.');
    }
}
