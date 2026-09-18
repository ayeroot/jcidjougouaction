@extends('layouts.app')
@section('title', 'Fiche postulant')

@section('content')
@php
    $labels = ['nouveau'=>'Nouveau','contacte'=>'Contacté','en_formation'=>'En formation','examen'=>'Examen','admis'=>'Admis','rejete'=>'Rejeté'];
    $peutGerer = $u->can('postulants.gerer');
@endphp

<a href="{{ route('postulants.index') }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour au recrutement</a>

<div class="grid md:grid-cols-3 gap-5 mt-3">
    <div class="md:col-span-2 space-y-5">
        <div class="bg-white border rounded-xl p-6">
            <h1 class="text-2xl font-bold text-jci-900">{{ $postulant->nom_complet }}</h1>
            <span class="inline-block mt-2 text-xs px-2 py-1 rounded bg-jci-100 text-jci-700">{{ $labels[$postulant->statut] ?? $postulant->statut }}</span>

            <div class="grid sm:grid-cols-2 gap-4 mt-6 text-sm">
                <div><div class="text-slate-400">Téléphone</div><div class="font-medium">{{ $postulant->telephone ?: '—' }}</div></div>
                <div><div class="text-slate-400">Email</div><div class="font-medium">{{ $postulant->email ?: '—' }}</div></div>
                <div><div class="text-slate-400">Ville</div><div class="font-medium">{{ $postulant->ville ?: '—' }}</div></div>
                <div><div class="text-slate-400">Date de naissance</div><div class="font-medium">{{ $postulant->date_naissance?->format('d/m/Y') ?: '—' }}</div></div>
            </div>

            @if ($postulant->motivation)
                <div class="mt-5">
                    <div class="text-slate-400 text-sm">Motivation</div>
                    <p class="mt-1 text-slate-700">{{ $postulant->motivation }}</p>
                </div>
            @endif
        </div>

        {{-- Mini-relevé des formations suivies --}}
        <div class="bg-white border rounded-xl p-6">
            <div class="flex items-center justify-between mb-1">
                <h2 class="font-semibold text-jci-900">Relevé des formations</h2>
                <a href="{{ route('postulants.releve', $postulant) }}" target="_blank" class="text-xs border px-3 py-1.5 rounded-lg hover:bg-slate-50">🖨️ Imprimer</a>
            </div>
            <p class="text-xs text-slate-400 mb-4">{{ $postulant->nombreFormationsSuivies() }} formation(s) suivie(s).</p>
            @forelse ($postulant->formations as $f)
                <div class="flex items-center justify-between py-2 border-b last:border-0 text-sm">
                    <div>
                        <div class="font-medium">{{ $f->titre }}</div>
                        <div class="text-xs text-slate-400">{{ $f->date_formation?->format('d/m/Y') }}</div>
                    </div>
                    @if ($f->pivot->present)
                        <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-700">Présent</span>
                    @else
                        <span class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-500">Absent</span>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-400">Aucune formation enregistrée (module Formations à venir).</p>
            @endforelse
        </div>
    </div>

    {{-- Actions pipeline --}}
    <div class="space-y-5">
        @if ($peutGerer)
            <div class="bg-white border rounded-xl p-6">
                <h2 class="font-semibold text-jci-900 mb-3">Faire avancer</h2>
                <form method="POST" action="{{ route('postulants.statut', $postulant) }}" class="space-y-3">
                    @csrf @method('PATCH')
                    <select name="statut" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
                        @foreach ($labels as $val => $lbl)
                            <option value="{{ $val }}" @selected($postulant->statut===$val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <button class="w-full bg-jci-600 text-white py-2 rounded-lg hover:bg-jci-700 text-sm">Mettre à jour le statut</button>
                </form>

                @if (!$postulant->membre_id)
                    <form method="POST" action="{{ route('postulants.convertir', $postulant) }}" class="mt-3 space-y-2"
                          onsubmit="return confirm('Intégrer ce postulant comme membre ?')">
                        @csrf
                        <label class="block text-xs font-medium text-slate-600">Nom de la promotion</label>
                        <input name="promotion" required placeholder="Ex : Promotion Excellence 2026"
                               class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
                        <p class="text-[11px] text-slate-400">L'intégration se fait après les formations et l'examen réussi.</p>
                        <button class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 text-sm">✓ Intégrer comme membre</button>
                    </form>
                @else
                    <div class="mt-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                        Déjà admis — <a href="{{ route('membres.show', $postulant->membre_id) }}" class="underline">voir la fiche membre</a>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white border rounded-xl p-6 text-sm text-slate-500">
                Consultation seule. La gestion du pipeline est réservée au VPCD et au Président.
            </div>
        @endif
    </div>
</div>
@endsection
