@extends('layouts.app')
@section('title', 'Site vitrine')
@section('content')

<h1 class="text-2xl font-bold text-jci-900 mb-1">Gestion du site vitrine</h1>
<p class="text-slate-500 text-sm mb-6">Modifiez les textes, le logo, les informations de contact et l'affichage des sections de la page d'accueil publique.</p>

@if ($errors->any())
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc pl-5 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.vitrine.update') }}" enctype="multipart/form-data" class="space-y-5">
    @csrf @method('PUT')

    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-jci-900 mb-4">Contenus</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Titre principal (hero)</label>
                <input name="vitrine_hero_titre" value="{{ old('vitrine_hero_titre', $valeurs['vitrine_hero_titre'] ?? '') }}"
                       placeholder="Développer le leadership des jeunes citoyens de Djougou."
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Sous-titre (hero)</label>
                <input name="vitrine_hero_sous_titre" value="{{ old('vitrine_hero_sous_titre', $valeurs['vitrine_hero_sous_titre'] ?? '') }}"
                       placeholder="Formation, projets d'impact et engagement citoyen au cœur de la Donga."
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Texte de mission</label>
                <textarea name="vitrine_mission" rows="3" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">{{ old('vitrine_mission', $valeurs['vitrine_mission'] ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Logo de la vitrine (facultatif)</label>
                @if (!empty($valeurs['vitrine_logo']))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($valeurs['vitrine_logo']) }}" class="h-12 mb-2 rounded border">
                @endif
                <input type="file" name="vitrine_logo" accept="image/*" class="w-full text-sm border rounded-lg px-3 py-2">
            </div>
        </div>
    </div>

    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-jci-900 mb-4">Informations de contact</h2>
        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Ville / Adresse</label>
                <input name="vitrine_contact_ville" value="{{ old('vitrine_contact_ville', $valeurs['vitrine_contact_ville'] ?? '') }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="vitrine_contact_email" value="{{ old('vitrine_contact_email', $valeurs['vitrine_contact_email'] ?? '') }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Téléphone</label>
                <input name="vitrine_contact_tel" value="{{ old('vitrine_contact_tel', $valeurs['vitrine_contact_tel'] ?? '') }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
        </div>
    </div>

    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-jci-900 mb-4">Sections affichées</h2>
        <div class="space-y-2">
            @foreach ($sections as $cle => $libelle)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="{{ $cle }}" value="1"
                           @checked(($valeurs[$cle] ?? '1') === '1')
                           class="rounded border-slate-300 text-jci-600 focus:ring-jci-600">
                    {{ $libelle }}
                </label>
            @endforeach
        </div>
    </div>

    <button class="bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Enregistrer</button>
    <a href="{{ route('home') }}" target="_blank" class="text-sm text-jci-600 hover:underline ml-3">Voir la vitrine ↗</a>
</form>
@endsection
