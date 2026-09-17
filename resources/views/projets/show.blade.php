@extends('layouts.app')
@section('title', 'Projet')
@section('content')
@php
    $label = ['a_venir'=>'À venir','en_cours'=>'En cours','termine'=>'Terminé'];
    $fmt = fn($n)=>number_format((float)$n,0,',',' ').' FCFA';
@endphp
<div class="flex items-center justify-between mb-4">
    <a href="{{ route('projets.index') }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour aux projets</a>
    <div class="flex gap-2">
        <a href="{{ route('projets.edit', $projet) }}" class="text-sm border px-4 py-2 rounded-lg hover:bg-slate-50">Modifier</a>
        <form method="POST" action="{{ route('projets.destroy', $projet) }}" onsubmit="return confirm('Supprimer ce projet ?')">
            @csrf @method('DELETE')
            <button class="text-sm border border-red-200 text-red-600 px-4 py-2 rounded-lg hover:bg-red-50">Supprimer</button>
        </form>
    </div>
</div>
<div class="bg-white border rounded-xl p-6">
    <div class="flex items-start justify-between">
        <h1 class="text-2xl font-bold text-jci-900">{{ $projet->titre }}</h1>
        <span class="text-xs px-2 py-1 rounded bg-jci-100 text-jci-700">{{ $label[$projet->statut] ?? $projet->statut }}</span>
    </div>
    <p class="mt-3 text-slate-700">{{ $projet->description }}</p>
    <div class="mt-5">
        <div class="flex justify-between text-sm mb-1"><span class="text-slate-500">Avancement</span><span class="font-medium">{{ $projet->avancement }}%</span></div>
        <div class="h-3 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-jci-600" style="width: {{ $projet->avancement }}%"></div></div>
    </div>
    <div class="grid sm:grid-cols-3 gap-4 mt-6 text-sm">
        <div><div class="text-slate-400">Responsable</div><div class="font-medium">{{ $projet->responsable?->nom_complet ?: '—' }}</div></div>
        <div><div class="text-slate-400">Recettes</div><div class="font-medium text-emerald-600">{{ $fmt($projet->contributions->sum('montant')) }}</div></div>
        <div><div class="text-slate-400">Dépenses</div><div class="font-medium text-red-600">{{ $fmt($projet->depenses->sum('montant')) }}</div></div>
    </div>
</div>
@endsection
