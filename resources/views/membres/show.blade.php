@extends('layouts.app')
@section('title', 'Fiche membre')

@section('content')
@php
    $peutGerer = $u->can('membres.gerer');
    // Champs restreints : visibles par le bureau habilité OU par l'intéressé lui-même.
    $peutVoirDetails = $peutGerer || ($u->membre_id === $membre->id);
    $statutsLabels = ['actif'=>'Actif','honoraire'=>'Honoraire','past_president'=>'Past-Président','membre_honneur'=>"Membre d'honneur"];
@endphp

<div class="flex items-center justify-between mb-5">
    <a href="{{ route('membres.index') }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour aux membres</a>
    @if ($peutGerer)
        <div class="flex gap-2">
            <a href="{{ route('membres.edit', $membre) }}" class="text-sm border px-4 py-2 rounded-lg hover:bg-slate-50">Modifier</a>
            <form method="POST" action="{{ route('membres.destroy', $membre) }}" onsubmit="return confirm('Supprimer ce membre ?')">
                @csrf @method('DELETE')
                <button class="text-sm border border-red-200 text-red-600 px-4 py-2 rounded-lg hover:bg-red-50">Supprimer</button>
            </form>
        </div>
    @endif
</div>

<div class="grid md:grid-cols-3 gap-5">
    {{-- Colonne principale --}}
    <div class="md:col-span-2 space-y-5">
        <div class="bg-white border rounded-xl p-6">
            <div class="flex items-center gap-4">
                @if ($membre->photo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($membre->photo) }}" alt="{{ $membre->nom_complet }}" class="w-16 h-16 rounded-full object-cover border">
                @else
                    <div class="w-16 h-16 rounded-full bg-jci-100 text-jci-700 grid place-items-center text-2xl font-bold">
                        {{ mb_substr($membre->prenom,0,1) }}{{ mb_substr($membre->nom,0,1) }}
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-jci-900">{{ $membre->nom_complet }}</h1>
                    <p class="text-slate-500">{{ $membre->fonction ?: 'Membre' }}</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4 mt-6 text-sm">
                <div><div class="text-slate-400">Statut</div><div class="font-medium">{{ $statutsLabels[$membre->statut] ?? $membre->statut }}</div></div>
                <div><div class="text-slate-400">Ville</div><div class="font-medium">{{ $membre->ville ?: '—' }}</div></div>
                <div><div class="text-slate-400">Âge</div><div class="font-medium">{{ $membre->age ? $membre->age.' ans' : '—' }}</div></div>
                <div><div class="text-slate-400">Adhésion</div><div class="font-medium">{{ $membre->date_adhesion?->format('d/m/Y') ?: '—' }}</div></div>
                <div><div class="text-slate-400">Promotion</div><div class="font-medium">{{ $membre->promotion ?: '—' }}</div></div>
            </div>
        </div>

        {{-- Informations restreintes --}}
        <div class="bg-white border rounded-xl p-6">
            <h2 class="font-semibold text-jci-900 mb-3">Coordonnées</h2>
            @if ($peutVoirDetails)
                <div class="grid sm:grid-cols-2 gap-4 text-sm">
                    <div><div class="text-slate-400">Téléphone</div><div class="font-medium">{{ $membre->telephone ?: '—' }}</div></div>
                    <div><div class="text-slate-400">Email</div><div class="font-medium">{{ $membre->email ?: '—' }}</div></div>
                    <div><div class="text-slate-400">Date de naissance</div><div class="font-medium">{{ $membre->date_naissance?->format('d/m/Y') ?: '—' }}</div></div>
                    <div><div class="text-slate-400">Adresse</div><div class="font-medium">{{ $membre->adresse ?: '—' }}</div></div>
                </div>
            @else
                <p class="text-sm text-slate-400 italic">
                    🔒 Les coordonnées personnelles sont réservées au bureau habilité et à l'intéressé.
                </p>
            @endif
        </div>
    </div>

    {{-- Colonne latérale : parcours / carrière --}}
    <div class="space-y-5">
        <div class="bg-white border rounded-xl p-6">
            <h2 class="font-semibold text-jci-900 mb-3">Cotisation</h2>
            @if ($membre->estHonorable())
                <span class="text-sm px-3 py-1 rounded bg-green-100 text-green-700">Membre honorable (à jour)</span>
            @else
                <span class="text-sm px-3 py-1 rounded bg-red-100 text-red-700">Non à jour de cotisation</span>
            @endif
            <p class="text-xs text-slate-400 mt-2">Statut calculé automatiquement d'après les cotisations du mandat en cours.</p>
        </div>

        <div class="bg-white border rounded-xl p-6">
            <h2 class="font-semibold text-jci-900 mb-3">Parcours / carrières</h2>
            @forelse ($membre->carrieres as $c)
                <div class="flex items-start gap-3 py-2 border-b last:border-0">
                    <span class="mt-1 w-2 h-2 rounded-full bg-jci-600"></span>
                    <div>
                        <div class="font-medium text-sm">{{ $c->type }}</div>
                        <div class="text-xs text-slate-400">{{ $c->annee }}{{ $c->description ? ' — '.$c->description : '' }}</div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400">Aucune carrière enregistrée.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
