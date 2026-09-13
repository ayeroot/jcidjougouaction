@extends('layouts.app')
@section('title', 'Formations')

@section('content')
@php
    $statutBadge = ['planifiee'=>'bg-blue-100 text-blue-700','realisee'=>'bg-green-100 text-green-700','annulee'=>'bg-red-100 text-red-700'];
    $statutLabel = ['planifiee'=>'Planifiée','realisee'=>'Réalisée','annulee'=>'Annulée'];
@endphp

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-jci-900">Formations</h1>
        <p class="text-slate-500 text-sm">{{ $formations->total() }} formation(s){{ $mois ? ' — mois filtré : '.$mois : '' }}</p>
    </div>
    <a href="{{ route('formations.create') }}" class="bg-jci-900 text-white font-semibold px-4 py-2 rounded-lg hover:bg-jci-700 text-sm">+ Planifier</a>
</div>

<form method="GET" class="bg-white border rounded-xl p-4 mb-5 flex flex-wrap items-end gap-3 text-sm">
    <div>
        <label class="block text-xs text-slate-500 mb-1">Formations du mois</label>
        <input type="month" name="mois" value="{{ $mois }}" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <button class="bg-jci-600 text-white px-4 py-2 rounded-lg hover:bg-jci-700">Filtrer</button>
    @if ($mois)<a href="{{ route('formations.index') }}" class="px-4 py-2 rounded-lg border hover:bg-slate-50">Tout voir</a>@endif
</form>

<div class="bg-white border rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600 text-left">
            <tr>
                <th class="px-4 py-3 font-semibold">Formation</th>
                <th class="px-4 py-3 font-semibold">Date</th>
                <th class="px-4 py-3 font-semibold">Formateur</th>
                <th class="px-4 py-3 font-semibold">Statut</th>
                <th class="px-4 py-3 font-semibold text-center">Présents</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse ($formations as $f)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-jci-900">{{ $f->titre }}</div>
                        <div class="text-xs text-slate-400">{{ $f->theme }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $f->date_formation?->format('d/m/Y H:i') ?: '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $f->formateur?->nom ?: '—' }}</td>
                    <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded {{ $statutBadge[$f->statut] ?? '' }}">{{ $statutLabel[$f->statut] ?? $f->statut }}</span></td>
                    <td class="px-4 py-3 text-center">{{ $f->presents()->count() }}</td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('formations.show', $f) }}" class="text-jci-600 hover:underline">Ouvrir</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Aucune formation.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $formations->links() }}</div>
@endsection
