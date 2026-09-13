@extends('layouts.app')
@section('title', 'Finances')

@section('content')
@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ').' FCFA';
    $tabs = [
        ['finances.index','Vue d\'ensemble'],
        ['finances.cotisations','Cotisations'],
        ['finances.contributions','Contributions'],
        ['finances.depenses','Dépenses'],
    ];
@endphp

<h1 class="text-2xl font-bold text-jci-900 mb-4">Finances / Trésorerie</h1>

<div class="flex flex-wrap gap-2 mb-6 text-sm">
    @foreach ($tabs as [$route,$label])
        <a href="{{ route($route) }}" class="px-4 py-2 rounded-lg border {{ request()->routeIs($route) ? 'bg-jci-900 text-white' : 'bg-white hover:bg-slate-50' }}">{{ $label }}</a>
    @endforeach
</div>

{{-- Solde --}}
<div class="bg-jci-900 text-white rounded-xl p-6 mb-5">
    <div class="text-sm text-jci-100">Solde de caisse (global)</div>
    <div class="text-4xl font-black mt-1">{{ $fmt($solde) }}</div>
    <div class="text-xs text-jci-100 mt-2">Cotisations + Contributions − Dépenses</div>
</div>

<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border rounded-xl p-5"><div class="text-sm text-slate-500">Cotisations</div><div class="text-2xl font-bold text-emerald-600 mt-1">{{ $fmt($totalCotisations) }}</div></div>
    <div class="bg-white border rounded-xl p-5"><div class="text-sm text-slate-500">Contributions</div><div class="text-2xl font-bold text-jci-700 mt-1">{{ $fmt($totalContributions) }}</div></div>
    <div class="bg-white border rounded-xl p-5"><div class="text-sm text-slate-500">Dépenses</div><div class="text-2xl font-bold text-red-600 mt-1">{{ $fmt($totalDepenses) }}</div></div>
</div>

<div class="grid lg:grid-cols-2 gap-5">
    {{-- Budget par projet --}}
    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-jci-900 mb-3">Budget par projet</h2>
        <table class="w-full text-sm">
            <thead class="text-slate-500 text-left border-b">
                <tr><th class="py-2">Projet</th><th class="py-2 text-right">Recettes</th><th class="py-2 text-right">Dépenses</th><th class="py-2 text-right">Solde</th></tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($projets as $p)
                    <tr>
                        <td class="py-2">{{ $p->titre }}</td>
                        <td class="py-2 text-right text-emerald-600">{{ $fmt($p->recettes) }}</td>
                        <td class="py-2 text-right text-red-600">{{ $fmt($p->depenses_total) }}</td>
                        <td class="py-2 text-right font-medium">{{ $fmt($p->recettes - $p->depenses_total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Membres honorables --}}
    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-jci-900 mb-3">Cotisations des membres</h2>
        <div class="flex gap-4">
            <div class="flex-1 bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                <div class="text-3xl font-black text-green-700">{{ $honorables }}</div>
                <div class="text-sm text-green-700">Honorables (à jour)</div>
            </div>
            <div class="flex-1 bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                <div class="text-3xl font-black text-red-700">{{ $nonHonorables }}</div>
                <div class="text-sm text-red-700">Non à jour</div>
            </div>
        </div>
        <a href="{{ route('finances.cotisations') }}" class="inline-block mt-4 text-sm text-jci-600 hover:underline">Gérer les cotisations →</a>
    </div>
</div>
@endsection
