@extends('layouts.app')
@section('title', 'Tableau de bord')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-jci-900">Bonjour, {{ explode(' ', $u->name)[0] }} 👋</h1>
    <p class="text-slate-500">
        @if ($mandat)
            Mandat {{ $mandat->annee }}@if($mandat->theme) — {{ $mandat->theme }}@endif
        @else
            Bienvenue dans l'espace de gestion.
        @endif
    </p>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @php
        $cards = [
            ['Membres', $stats['membres'], 'text-jci-700'],
            ['Postulants actifs', $stats['postulants_actifs'], 'text-amber-600'],
            ['Formations ce mois', $stats['formations_mois'], 'text-emerald-600'],
            ['Projets en cours', $stats['projets_en_cours'], 'text-purple-600'],
        ];
    @endphp
    @foreach ($cards as [$label, $val, $color])
        <div class="bg-white border rounded-xl p-5">
            <div class="text-sm text-slate-500">{{ $label }}</div>
            <div class="text-3xl font-black mt-1 {{ $color }}">{{ $val }}</div>
        </div>
    @endforeach
</div>

{{-- Solde de caisse : visible uniquement pour les rôles financiers --}}
@role('tresorier|president')
    <div class="mt-4 bg-jci-900 text-white rounded-xl p-5 flex items-center justify-between">
        <div>
            <div class="text-sm text-jci-100">Solde de caisse (global)</div>
            <div class="text-3xl font-black mt-1">{{ number_format($solde, 0, ',', ' ') }} FCFA</div>
        </div>
        <span class="text-xs bg-white/10 px-2 py-1 rounded">Réservé Trésorier / Président</span>
    </div>
@endrole

<div class="mt-6 grid md:grid-cols-2 gap-4">
    <a href="{{ route('membres.index') }}" class="bg-white border rounded-xl p-5 hover:shadow-md transition">
        <div class="font-semibold text-jci-900">Membres</div>
        <p class="text-sm text-slate-500 mt-1">Consulter l'annuaire, filtrer, voir les parcours.</p>
    </a>
    @role('vpcd|vpf|president')
    <a href="{{ route('postulants.index') }}" class="bg-white border rounded-xl p-5 hover:shadow-md transition">
        <div class="font-semibold text-jci-900">Recrutement</div>
        <p class="text-sm text-slate-500 mt-1">Suivre les postulants et faire avancer le pipeline.</p>
    </a>
    @endrole
    @role('vpf|president')
    <a href="{{ route('formations.index') }}" class="bg-white border rounded-xl p-5 hover:shadow-md transition">
        <div class="font-semibold text-jci-900">Formations</div>
        <p class="text-sm text-slate-500 mt-1">Planifier, pointer les présences, rédiger les rapports.</p>
    </a>
    @endrole
    @role('tresorier|president')
    <a href="{{ route('finances.index') }}" class="bg-white border rounded-xl p-5 hover:shadow-md transition">
        <div class="font-semibold text-jci-900">Finances</div>
        <p class="text-sm text-slate-500 mt-1">Cotisations, contributions, dépenses et solde.</p>
    </a>
    @endrole
</div>
@endsection
