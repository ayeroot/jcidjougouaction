@extends('layouts.app')
@section('title', 'Archives')
@section('content')
@php
    $typeLabel = ['rapport'=>'Rapport','photo'=>'Photo','document'=>'Document','autre'=>'Autre'];
    $typeBadge = ['rapport'=>'bg-blue-100 text-blue-700','photo'=>'bg-purple-100 text-purple-700','document'=>'bg-slate-100 text-slate-700','autre'=>'bg-amber-100 text-amber-700'];
@endphp
<h1 class="text-2xl font-bold text-jci-900 mb-5">Archives & Documentation</h1>
<div class="grid lg:grid-cols-3 gap-5">
    <div class="bg-white border rounded-xl p-6 h-fit">
        <h2 class="font-semibold text-jci-900 mb-3">Archiver un document</h2>
        <form method="POST" action="{{ route('archives.store') }}" class="space-y-3">
            @csrf
            <div><label class="block text-sm font-medium mb-1">Titre *</label>
                <input name="titre" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none"></div>
            <div><label class="block text-sm font-medium mb-1">Type *</label>
                <select name="type" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
                    @foreach ($typeLabel as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                </select></div>
            <div><label class="block text-sm font-medium mb-1">Référence / lien</label>
                <input name="reference" placeholder="Cote de classement ou URL" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none"></div>
            <div><label class="block text-sm font-medium mb-1">Date</label>
                <input type="date" name="date_document" value="{{ date('Y-m-d') }}" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none"></div>
            <div><label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none"></textarea></div>
            <button class="w-full bg-jci-900 text-white py-2 rounded-lg hover:bg-jci-700 text-sm">Archiver</button>
        </form>
    </div>
    <div class="lg:col-span-2">
        <form method="GET" class="mb-3 flex flex-wrap gap-2 text-sm">
            <a href="{{ route('archives.index') }}" class="px-3 py-1.5 rounded-lg border {{ !request('type') ? 'bg-jci-900 text-white' : 'bg-white hover:bg-slate-50' }}">Tout</a>
            @foreach ($typeLabel as $v=>$l)
                <a href="{{ route('archives.index',['type'=>$v]) }}" class="px-3 py-1.5 rounded-lg border {{ request('type')===$v ? 'bg-jci-900 text-white' : 'bg-white hover:bg-slate-50' }}">{{ $l }}</a>
            @endforeach
        </form>
        <div class="bg-white border rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-left">
                    <tr><th class="px-4 py-3">Titre</th><th class="px-4 py-3">Type</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Réf.</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($archives as $a)
                        <tr>
                            <td class="px-4 py-3"><div class="font-medium text-jci-900">{{ $a->titre }}</div><div class="text-xs text-slate-400">{{ $a->description }}</div></td>
                            <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded {{ $typeBadge[$a->type] ?? '' }}">{{ $typeLabel[$a->type] ?? $a->type }}</span></td>
                            <td class="px-4 py-3 text-slate-500">{{ $a->date_document?->format('d/m/Y') ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs">{{ $a->reference ?: '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('archives.destroy', $a) }}" onsubmit="return confirm('Supprimer ?')">
                                    @csrf @method('DELETE')<button class="text-red-600 hover:underline">Suppr.</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Aucune archive.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $archives->links() }}</div>
    </div>
</div>
@endsection
