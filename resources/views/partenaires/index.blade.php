@extends('layouts.app')
@section('title', 'Partenaires')
@section('content')
<h1 class="text-2xl font-bold text-jci-900 mb-5">Partenaires</h1>
<div class="grid lg:grid-cols-3 gap-5">
    <div class="bg-white border rounded-xl p-6 h-fit">
        <h2 class="font-semibold text-jci-900 mb-3">Ajouter un partenaire</h2>
        <form method="POST" action="{{ route('partenaires.store') }}" class="space-y-3">
            @csrf
            <div><label class="block text-sm font-medium mb-1">Nom *</label>
                <input name="nom" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none"></div>
            <div><label class="block text-sm font-medium mb-1">Type</label>
                <input name="type" placeholder="Institution, Média, Entreprise…" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none"></div>
            <div><label class="block text-sm font-medium mb-1">Contact</label>
                <input name="contact" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none"></div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="public" value="1" checked class="rounded border-slate-300"> Visible sur la vitrine</label>
            <button class="w-full bg-jci-900 text-white py-2 rounded-lg hover:bg-jci-700 text-sm">Ajouter</button>
        </form>
    </div>
    <div class="bg-white border rounded-xl overflow-hidden lg:col-span-2">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 text-left">
                <tr><th class="px-4 py-3">Partenaire</th><th class="px-4 py-3">Type</th><th class="px-4 py-3">Contact</th><th class="px-4 py-3 text-center">Contrib.</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($partenaires as $p)
                    <tr>
                        <td class="px-4 py-3 font-medium text-jci-900">{{ $p->nom }} @unless($p->public)<span class="text-xs text-slate-400">(privé)</span>@endunless</td>
                        <td class="px-4 py-3 text-slate-500">{{ $p->type ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $p->contact ?: '—' }}</td>
                        <td class="px-4 py-3 text-center">{{ $p->contributions_count }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('partenaires.edit', $p) }}" class="text-jci-600 hover:underline">Modifier</a>
                            <form method="POST" action="{{ route('partenaires.destroy', $p) }}" class="inline" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')<button class="text-red-600 hover:underline ml-2">Suppr.</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Aucun partenaire.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $partenaires->links() }}</div>
@endsection
