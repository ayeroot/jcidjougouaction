@extends('layouts.app')
@section('title', 'Membres')

@section('content')
@php
    $peutGerer = $u->hasAnyRole(['vpm', 'president']);
    $statutsLabels = ['actif'=>'Actif','honoraire'=>'Honoraire','past_president'=>'Past-Président','membre_honneur'=>"Membre d'honneur"];
@endphp

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-jci-900">Membres</h1>
        <p class="text-slate-500 text-sm">{{ $membres->total() }} membre(s)</p>
    </div>
    @if ($peutGerer)
        <a href="{{ route('membres.create') }}" class="bg-jci-900 text-white font-semibold px-4 py-2 rounded-lg hover:bg-jci-700 text-sm">
            + Nouveau membre
        </a>
    @endif
</div>

{{-- Filtres --}}
<form method="GET" class="bg-white border rounded-xl p-4 mb-5 grid sm:grid-cols-2 lg:grid-cols-5 gap-3 text-sm">
    <input name="recherche" value="{{ request('recherche') }}" placeholder="Nom ou prénom…"
           class="border rounded-lg px-3 py-2 lg:col-span-2 focus:ring-2 focus:ring-jci-600 outline-none">
    <select name="statut" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
        <option value="">Tous les statuts</option>
        @foreach ($statutsLabels as $val => $lbl)
            <option value="{{ $val }}" @selected(request('statut')===$val)>{{ $lbl }}</option>
        @endforeach
    </select>
    <input name="carriere" value="{{ request('carriere') }}" placeholder="Carrière (MC, protocole…)"
           class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    <label class="flex items-center gap-2 px-2">
        <input type="checkbox" name="moins40" value="1" @checked(request('moins40')) class="rounded border-slate-300">
        Moins de 40 ans
    </label>
    <div class="lg:col-span-5 flex gap-2">
        <button class="bg-jci-600 text-white px-4 py-2 rounded-lg hover:bg-jci-700">Filtrer</button>
        <a href="{{ route('membres.index') }}" class="px-4 py-2 rounded-lg border hover:bg-slate-50">Réinitialiser</a>
    </div>
</form>

{{-- Tableau --}}
<div class="bg-white border rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600 text-left">
            <tr>
                <th class="px-4 py-3 font-semibold">Membre</th>
                <th class="px-4 py-3 font-semibold">Fonction</th>
                <th class="px-4 py-3 font-semibold">Statut</th>
                <th class="px-4 py-3 font-semibold">Ville</th>
                <th class="px-4 py-3 font-semibold text-center">Cotisation</th>
                @if ($peutGerer) <th class="px-4 py-3 font-semibold">Contact</th> @endif
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse ($membres as $membre)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-jci-900">{{ $membre->nom_complet }}</div>
                        <div class="text-xs text-slate-400">{{ $membre->age ? $membre->age.' ans' : '—' }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $membre->fonction ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-1 rounded bg-jci-100 text-jci-700">{{ $statutsLabels[$membre->statut] ?? $membre->statut }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $membre->ville ?: '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if ($membre->estHonorable())
                            <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-700">Honorable</span>
                        @else
                            <span class="text-xs px-2 py-1 rounded bg-red-100 text-red-700">Non à jour</span>
                        @endif
                    </td>
                    @if ($peutGerer)
                        <td class="px-4 py-3 text-slate-600 text-xs">
                            {{ $membre->telephone ?: '—' }}<br>{{ $membre->email }}
                        </td>
                    @endif
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('membres.show', $membre) }}" class="text-jci-600 hover:underline">Voir</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">Aucun membre trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $membres->links() }}</div>
@endsection
