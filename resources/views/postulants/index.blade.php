@extends('layouts.app')
@section('title', 'Recrutement')

@section('content')
@php
    $labels = ['nouveau'=>'Nouveau','contacte'=>'Contacté','en_formation'=>'En formation','admis'=>'Admis','rejete'=>'Rejeté'];
    $colors = ['nouveau'=>'bg-blue-100 text-blue-700','contacte'=>'bg-amber-100 text-amber-700','en_formation'=>'bg-purple-100 text-purple-700','admis'=>'bg-green-100 text-green-700','rejete'=>'bg-red-100 text-red-700'];
@endphp

<div class="mb-5">
    <h1 class="text-2xl font-bold text-jci-900">Recrutement</h1>
    <p class="text-slate-500 text-sm">{{ $postulants->total() }} postulant(s) — pipeline de candidature</p>
</div>

<form method="GET" class="mb-5 flex flex-wrap gap-2 text-sm">
    <a href="{{ route('postulants.index') }}" class="px-3 py-1.5 rounded-lg border {{ !request('statut') ? 'bg-jci-900 text-white' : 'hover:bg-slate-50' }}">Tous</a>
    @foreach ($labels as $val => $lbl)
        <a href="{{ route('postulants.index', ['statut'=>$val]) }}" class="px-3 py-1.5 rounded-lg border {{ request('statut')===$val ? 'bg-jci-900 text-white' : 'hover:bg-slate-50' }}">{{ $lbl }}</a>
    @endforeach
</form>

<div class="bg-white border rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600 text-left">
            <tr>
                <th class="px-4 py-3 font-semibold">Postulant</th>
                <th class="px-4 py-3 font-semibold">Contact</th>
                <th class="px-4 py-3 font-semibold">Ville</th>
                <th class="px-4 py-3 font-semibold">Statut</th>
                <th class="px-4 py-3 font-semibold">Inscrit le</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse ($postulants as $p)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium text-jci-900">{{ $p->nom_complet }}</td>
                    <td class="px-4 py-3 text-slate-600 text-xs">{{ $p->telephone }}<br>{{ $p->email }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $p->ville ?: '—' }}</td>
                    <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded {{ $colors[$p->statut] ?? '' }}">{{ $labels[$p->statut] ?? $p->statut }}</span></td>
                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $p->created_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('postulants.show', $p) }}" class="text-jci-600 hover:underline">Voir</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Aucun postulant.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $postulants->links() }}</div>
@endsection
