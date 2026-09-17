@extends('layouts.app')
@section('title', 'Mandat')
@section('content')
<div class="flex items-center justify-between mb-4">
    <a href="{{ route('mandats.index') }}" class="text-sm text-slate-500 hover:text-jci-900">← Historique des mandats</a>
    <div class="flex gap-2">
        @unless ($mandat->actif)
            <form method="POST" action="{{ route('mandats.actif', $mandat) }}">@csrf @method('PATCH')
                <button class="text-sm border px-4 py-2 rounded-lg hover:bg-slate-50">Définir comme actif</button>
            </form>
        @endunless
        <a href="{{ route('mandats.edit', $mandat) }}" class="text-sm bg-jci-900 text-white px-4 py-2 rounded-lg hover:bg-jci-700">Configurer</a>
    </div>
</div>

<div class="bg-white border rounded-xl p-6 mb-5">
    <div class="flex items-center gap-4">
        @if ($mandat->logo)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($mandat->logo) }}" class="w-16 h-16 rounded-lg object-contain border">
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

<div class="grid lg:grid-cols-2 gap-5">
    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-jci-900 mb-3">Photo de famille du CDL</h2>
        @if ($mandat->photo_famille)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($mandat->photo_famille) }}" class="w-full rounded-lg border">
        @else
            <p class="text-sm text-slate-400 italic">Aucune photo de famille pour ce mandat.</p>
        @endif
    </div>
    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-jci-900 mb-3">Membres du CDL ({{ $cdl->count() }})</h2>
        @forelse ($cdl as $m)
            <div class="flex items-center gap-3 py-2 border-b last:border-0">
                @if ($m->photo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($m->photo) }}" class="w-9 h-9 rounded-full object-cover border">
                @else
                    <div class="w-9 h-9 rounded-full bg-jci-100 text-jci-700 grid place-items-center text-xs font-bold">{{ mb_substr($m->nom,0,2) }}</div>
                @endif
                <div><div class="text-sm font-medium">{{ $m->fonction }}</div><div class="text-xs text-slate-400">{{ $m->nom_complet ?: '—' }}</div></div>
            </div>
        @empty
            <p class="text-sm text-slate-400">Aucun membre du CDL enregistré.</p>
        @endforelse
    </div>
</div>
@endsection
