@extends('layouts.app')
@section('title', 'Mandat')
@section('content')
@php $stImg = fn($p) => \Illuminate\Support\Facades\Storage::disk('public')->url($p); @endphp

<div class="flex items-center justify-between mb-4">
    <a href="{{ route('mandats.index') }}" class="text-sm text-slate-500 hover:text-jci-900">← Historique des mandats</a>
    @can('mandats.gerer')
    <div class="flex gap-2">
        @unless ($mandat->actif)
            <form method="POST" action="{{ route('mandats.actif', $mandat) }}">@csrf @method('PATCH')
                <button class="text-sm border px-4 py-2 rounded-lg hover:bg-slate-50">Définir comme actif</button>
            </form>
        @endunless
        <a href="{{ route('mandats.edit', $mandat) }}" class="text-sm bg-jci-900 text-white px-4 py-2 rounded-lg hover:bg-jci-700">Configurer</a>
    </div>
    @endcan
</div>

@if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc pl-5 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="bg-white border rounded-xl p-6 mb-5">
    <div class="flex items-center gap-4">
        @if ($mandat->logo)
            <img src="{{ $stImg($mandat->logo) }}" class="w-16 h-16 rounded-lg object-contain border">
        @else
            <div class="w-16 h-16 rounded-lg grid place-items-center text-white font-bold text-lg" style="background: {{ $mandat->couleurOuDefaut() }}">{{ $mandat->annee }}</div>
        @endif
        <div>
            <h1 class="text-2xl font-bold text-jci-900">Mandat {{ $mandat->annee }} @if($mandat->actif)<span class="text-xs align-middle px-2 py-1 rounded bg-green-100 text-green-700">Actif</span>@endif</h1>
            <p class="text-slate-500">{{ $mandat->theme ?: 'Thème non défini' }}</p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <span class="text-xs text-slate-400">Couleur</span>
            <span class="w-6 h-6 rounded-full border" style="background: {{ $mandat->couleurOuDefaut() }}"></span>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-5 mb-5">
    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-jci-900 mb-3">Photo de famille du CDL</h2>
        @if ($mandat->photo_famille)
            <img src="{{ $stImg($mandat->photo_famille) }}" class="w-full rounded-lg border">
        @else
            <p class="text-sm text-slate-400 italic">Aucune photo de famille pour ce mandat.</p>
        @endif
    </div>
    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-jci-900 mb-3">Membres du CDL ({{ $affectations->count() }})</h2>
        @forelse ($affectations as $poste => $aff)
            <div class="flex items-center gap-3 py-2 border-b last:border-0">
                @if ($aff->membre)
                    <img src="{{ $aff->membre->photo_url }}" alt="" class="w-9 h-9 rounded-full object-cover border">
                @else
                    <div class="w-9 h-9 rounded-full bg-jci-100 text-jci-700 grid place-items-center text-xs font-bold">?</div>
                @endif
                <div><div class="text-sm font-medium">{{ $poste }}</div><div class="text-xs text-slate-400">{{ $aff->membre?->nom_complet ?: '—' }}</div></div>
            </div>
        @empty
            <p class="text-sm text-slate-400">Aucun poste affecté pour ce mandat.</p>
        @endforelse
    </div>
</div>

{{-- Configuration du CDL : affectation des postes (Président uniquement) --}}
@can('mandats.gerer')
<div class="bg-white border rounded-xl p-6">
    <h2 class="font-semibold text-jci-900 mb-1">Configuration du CDL — affectation des postes</h2>
    <p class="text-xs text-slate-400 mb-4">Affecter un membre à un poste crée automatiquement son compte (avec le rôle correspondant) et lui envoie un email d'activation. Un seul membre par poste et par année ; l'ancien titulaire est automatiquement désactivé.</p>

    <div class="space-y-2">
        @foreach (\App\Models\AffectationCdl::postes() as $poste)
            @php $actuel = $affectations[$poste] ?? null; @endphp
            <form method="POST" action="{{ route('cdl.affecter', $mandat) }}" class="flex flex-wrap items-center gap-2 py-2 border-b last:border-0">
                @csrf
                <input type="hidden" name="poste" value="{{ $poste }}">
                <div class="w-56 text-sm font-medium text-jci-900">{{ $poste }}</div>
                <select name="membre_id" required class="flex-1 min-w-[180px] border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
                    <option value="">— Choisir un membre —</option>
                    @foreach ($membres as $m)
                        <option value="{{ $m->id }}" @selected($actuel && $actuel->membre_id == $m->id)>{{ $m->nom_complet }}</option>
                    @endforeach
                </select>
                <button class="bg-jci-600 text-white px-4 py-2 rounded-lg hover:bg-jci-700 text-sm">{{ $actuel ? 'Changer' : 'Affecter' }}</button>
                @if ($actuel)
                    <span class="text-xs text-green-600">✓ {{ $actuel->membre?->nom_complet }}</span>
                @endif
            </form>
        @endforeach
    </div>
</div>
@endcan
@endsection
