@extends('layouts.app')
@section('title', 'Efficacité 100%')

@section('content')
@php
    $statutLabel = ['a_faire'=>'À faire','en_cours'=>'En cours','fait'=>'Fait'];
    $statutBadge = ['a_faire'=>'bg-slate-100 text-slate-600','en_cours'=>'bg-amber-100 text-amber-700','fait'=>'bg-green-100 text-green-700'];
@endphp

<h1 class="text-2xl font-bold text-jci-900 mb-1">Plan d'action & Efficacité 100%</h1>
<p class="text-slate-500 text-sm mb-5">Suivi des standards d'efficacité et du plan d'action du mandat.</p>

{{-- Progression --}}
<div class="bg-white border rounded-xl p-6 mb-6">
    <div class="flex items-center justify-between mb-2">
        <span class="font-semibold text-jci-900">Progression des standards</span>
        <span class="text-2xl font-black text-jci-700">{{ $pourcentage }}%</span>
    </div>
    <div class="h-4 bg-slate-100 rounded-full overflow-hidden">
        <div class="h-full {{ $pourcentage==100 ? 'bg-green-500' : 'bg-jci-600' }}" style="width: {{ $pourcentage }}%"></div>
    </div>
    <p class="text-xs text-slate-400 mt-2">{{ $atteints }} / {{ $total }} standards atteints</p>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- Standards --}}
    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-jci-900 mb-3">Standards d'efficacité</h2>
        <div class="space-y-1 mb-4">
            @forelse ($standards as $s)
                <div class="flex items-center gap-3 py-2 border-b last:border-0">
                    <form method="POST" action="{{ route('efficacite.standards.toggle', $s) }}">
                        @csrf @method('PATCH')
                        <button title="Basculer" class="w-6 h-6 rounded border grid place-items-center {{ $s->atteint ? 'bg-green-500 border-green-500 text-white' : 'border-slate-300 text-transparent hover:border-jci-600' }}">✓</button>
                    </form>
                    <div class="flex-1">
                        <div class="text-sm {{ $s->atteint ? 'line-through text-slate-400' : '' }}">{{ $s->libelle }}</div>
                        @if ($s->categorie)<div class="text-xs text-slate-400">{{ $s->categorie }}</div>@endif
                    </div>
                    <form method="POST" action="{{ route('efficacite.standards.destroy', $s) }}" onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')<button class="text-slate-300 hover:text-red-500 text-sm">✕</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-slate-400">Aucun standard défini.</p>
            @endforelse
        </div>
        <form method="POST" action="{{ route('efficacite.standards.store') }}" class="flex gap-2">
            @csrf
            <input name="libelle" required placeholder="Nouveau standard…" class="flex-1 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
            <button class="bg-jci-600 text-white px-4 rounded-lg hover:bg-jci-700 text-sm">+</button>
        </form>
    </div>

    {{-- Plan d'action --}}
    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-jci-900 mb-3">Plan d'action</h2>
        <div class="space-y-2 mb-4 max-h-72 overflow-auto pr-1">
            @forelse ($actions as $a)
                <div class="flex items-center gap-3 py-2 border-b last:border-0">
                    <div class="flex-1">
                        <div class="text-sm font-medium">{{ $a->titre }}</div>
                        <div class="text-xs text-slate-400">{{ $a->mois }}{{ $a->description ? ' — '.$a->description : '' }}</div>
                    </div>
                    <form method="POST" action="{{ route('efficacite.actions.update', $a) }}">
                        @csrf @method('PATCH')
                        <select name="statut" onchange="this.form.submit()" class="text-xs border rounded px-2 py-1 {{ $statutBadge[$a->statut] ?? '' }}">
                            @foreach ($statutLabel as $v=>$l)<option value="{{ $v }}" @selected($a->statut===$v)>{{ $l }}</option>@endforeach
                        </select>
                    </form>
                    <form method="POST" action="{{ route('efficacite.actions.destroy', $a) }}" onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')<button class="text-slate-300 hover:text-red-500 text-sm">✕</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-slate-400">Aucune action planifiée.</p>
            @endforelse
        </div>
        <form method="POST" action="{{ route('efficacite.actions.store') }}" class="space-y-2">
            @csrf
            <input name="titre" required placeholder="Nouvelle action…" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
            <div class="flex gap-2">
                <input type="month" name="mois" value="{{ date('Y-m') }}" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
                <select name="statut" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
                    @foreach ($statutLabel as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                </select>
                <button class="flex-1 bg-jci-900 text-white rounded-lg hover:bg-jci-700 text-sm">Ajouter</button>
            </div>
        </form>
    </div>
</div>
@endsection
