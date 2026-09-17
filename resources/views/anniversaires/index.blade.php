@extends('layouts.app')
@section('title', 'Anniversaires')

@section('content')
@php
    $moisNoms = [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
@endphp

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-jci-900">🎂 Anniversaires</h1>
        <p class="text-slate-500 text-sm">{{ $membres->count() }} membre(s) fêté(s) en {{ $moisNoms[$mois] }}</p>
    </div>
    <form method="GET">
        <select name="mois" onchange="this.form.submit()" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
            @foreach ($moisNoms as $num => $nom)
                <option value="{{ $num }}" @selected($mois===$num)>{{ $nom }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($membres as $m)
        <div class="bg-white border rounded-xl p-5 flex items-center gap-4">
            <div class="text-center leading-none">
                <div class="text-3xl font-black text-jci-700">{{ $m->date_naissance->day }}</div>
                <div class="text-xs text-slate-400 uppercase">{{ substr($moisNoms[$mois],0,3) }}</div>
            </div>
            <div class="border-l pl-4">
                <div class="font-semibold text-jci-900">{{ $m->nom_complet }}</div>
                <div class="text-xs text-slate-500">{{ $m->fonction ?: 'Membre' }}</div>
                <div class="text-xs text-slate-400 mt-1">Atteindra {{ $m->age_anniversaire }} ans</div>
            </div>
        </div>
    @empty
        <p class="text-slate-400 col-span-full text-center py-10">Aucun anniversaire ce mois-ci.</p>
    @endforelse
</div>
@endsection
