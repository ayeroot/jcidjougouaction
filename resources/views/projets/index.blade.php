@extends('layouts.app')
@section('title', 'Projets')
@section('content')
@php
    $badge = ['a_venir'=>'bg-amber-100 text-amber-700','en_cours'=>'bg-blue-100 text-blue-700','termine'=>'bg-green-100 text-green-700'];
    $label = ['a_venir'=>'À venir','en_cours'=>'En cours','termine'=>'Terminé'];
@endphp
<div class="flex items-center justify-between mb-5">
    <div><h1 class="text-2xl font-bold text-jci-900">Projets</h1><p class="text-slate-500 text-sm">{{ $projets->total() }} projet(s)</p></div>
    <a href="{{ route('projets.create') }}" class="bg-jci-900 text-white font-semibold px-4 py-2 rounded-lg hover:bg-jci-700 text-sm">+ Nouveau projet</a>
</div>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse ($projets as $p)
        <a href="{{ route('projets.show', $p) }}" class="bg-white border rounded-xl p-5 hover:shadow-md transition block">
            <div class="flex items-center justify-between">
                <span class="text-xs px-2 py-1 rounded {{ $badge[$p->statut] ?? '' }}">{{ $label[$p->statut] ?? $p->statut }}</span>
                @unless($p->public)<span class="text-xs text-slate-400">privé</span>@endunless
            </div>
            <h3 class="mt-3 font-semibold text-jci-900">{{ $p->titre }}</h3>
            <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $p->description }}</p>
            <div class="mt-3">
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-jci-600" style="width: {{ $p->avancement }}%"></div></div>
                <div class="text-xs text-slate-400 mt-1">{{ $p->avancement }}% — {{ $p->responsable?->nom_complet ?: 'Sans responsable' }}</div>
            </div>
        </a>
    @empty
        <p class="text-slate-400 col-span-full text-center py-10">Aucun projet.</p>
    @endforelse
</div>
<div class="mt-4">{{ $projets->links() }}</div>
@endsection
