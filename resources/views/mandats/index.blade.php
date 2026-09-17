@extends('layouts.app')
@section('title', 'Mandats')
@section('content')
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-jci-900">Mandats — historique</h1>
        <p class="text-slate-500 text-sm">Configuration et historique des mandats de l'organisation.</p>
    </div>
    <a href="{{ route('mandats.create') }}" class="bg-jci-900 text-white font-semibold px-4 py-2 rounded-lg hover:bg-jci-700 text-sm">+ Nouveau mandat</a>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
    @foreach ($mandats as $m)
        <a href="{{ route('mandats.show', $m) }}" class="bg-white border rounded-xl p-5 hover:shadow-md transition block">
            <div class="flex items-center justify-between">
                <div class="w-12 h-12 rounded-lg grid place-items-center text-white font-bold" style="background: {{ $m->couleurOuDefaut() }}">
                    {{ $m->annee }}
                </div>
                @if ($m->actif)
                    <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-700">Actif</span>
                @endif
            </div>
            <h2 class="mt-3 font-semibold text-jci-900">Mandat {{ $m->annee }}</h2>
            <p class="text-sm text-slate-500">{{ $m->theme ?: 'Thème non défini' }}</p>
        </a>
    @endforeach
</div>
@endsection
