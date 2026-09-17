@extends('layouts.app')
@section('title', 'Historique des activités')
@section('content')
@php
    $actionLabel = ['created'=>'Création','updated'=>'Modification','deleted'=>'Suppression'];
    $actionBadge = ['created'=>'bg-green-100 text-green-700','updated'=>'bg-amber-100 text-amber-700','deleted'=>'bg-red-100 text-red-700'];
    $entites = \App\Http\Controllers\HistoriqueController::ENTITES;
@endphp
<h1 class="text-2xl font-bold text-jci-900 mb-1">Historique des activités</h1>
<p class="text-slate-500 text-sm mb-5">Journal d'audit : chaque action est tracée (qui, quoi, quand).</p>

<div class="bg-white border rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600 text-left">
            <tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Auteur</th><th class="px-4 py-3">Action</th><th class="px-4 py-3">Élément</th></tr>
        </thead>
        <tbody class="divide-y">
            @forelse ($logs as $log)
                <tr>
                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">{{ $log->user?->name ?? 'Système' }}</td>
                    <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded {{ $actionBadge[$log->action] ?? '' }}">{{ $actionLabel[$log->action] ?? $log->action }}</span></td>
                    <td class="px-4 py-3 text-slate-600">{{ $entites[$log->auditable_type] ?? class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Aucune activité enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
