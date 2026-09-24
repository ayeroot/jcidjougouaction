@extends('layouts.app')
@section('title', 'Recettes / Dons')
@section('content')
@php $fmt = fn($n)=>number_format((float)$n,0,',',' ').' FCFA'; @endphp
<h1 class="text-2xl font-bold text-jci-900 mb-4">Finances / Trésorerie</h1>
@include('finances._tabs')

<div class="grid lg:grid-cols-3 gap-5">
    <div class="bg-white border rounded-xl p-6 h-fit">
        <h2 class="font-semibold text-jci-900 mb-3">Enregistrer une recette</h2>
        @can('finances.gerer')
        <form method="POST" action="{{ route('finances.contributions.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Type de financement</label>
                <select name="type" id="type-recette" required onchange="majChampsRecette()" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
                    @foreach ($types as $v=>$l)<option value="{{ $v }}" @selected(old('type')===$v)>{{ $l }}</option>@endforeach
                </select>
            </div>

            {{-- Membre (participation / contribution) --}}
            <div data-champ="membre">
                <label class="block text-sm font-medium mb-1">Membre</label>
                <select name="membre_id" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
                    <option value="">— Choisir —</option>
                    @foreach ($membres as $m)<option value="{{ $m->id }}" @selected(old('membre_id')==$m->id)>{{ $m->nom_complet }}</option>@endforeach
                </select>
            </div>

            {{-- Don d'un particulier --}}
            <div data-champ="donateur">
                <label class="block text-sm font-medium mb-1">Nom du donateur</label>
                <input name="donateur" value="{{ old('donateur') }}" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
            </div>

            {{-- Don d'un partenaire --}}
            <div data-champ="partenaire">
                <label class="block text-sm font-medium mb-1">Partenaire</label>
                <select name="partenaire_id" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
                    <option value="">— Choisir —</option>
                    @foreach ($partenaires as $p)<option value="{{ $p->id }}" @selected(old('partenaire_id')==$p->id)>{{ $p->nom }}</option>@endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Note / précision (facultatif)</label>
                <input name="source" value="{{ old('source') }}" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Projet (facultatif = global)</label>
                <select name="projet_id" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
                    <option value="">— Global —</option>
                    @foreach ($projets as $p)<option value="{{ $p->id }}" @selected(old('projet_id')==$p->id)>{{ $p->titre }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Montant (FCFA)</label>
                <input type="number" name="montant" step="1" min="0" value="{{ old('montant') }}" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Date</label>
                <input type="date" name="date_contribution" value="{{ old('date_contribution', date('Y-m-d')) }}" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <button class="w-full bg-jci-900 text-white py-2 rounded-lg hover:bg-jci-700 text-sm">Enregistrer</button>
        </form>
        <script>
            function majChampsRecette() {
                const t = document.getElementById('type-recette').value;
                const show = {
                    membre:     ['participation_membre','contribution_membre'].includes(t),
                    donateur:   t === 'don_particulier',
                    partenaire: t === 'don_partenaire',
                };
                document.querySelectorAll('[data-champ]').forEach(el => {
                    el.style.display = show[el.dataset.champ] ? '' : 'none';
                });
            }
            majChampsRecette();
        </script>
        @else
            <p class="text-sm text-slate-500">Consultation seule : seul le Trésorier peut enregistrer des mouvements financiers.</p>
        @endcan
    </div>

    <div class="bg-white border rounded-xl overflow-hidden lg:col-span-2">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 text-left">
                <tr><th class="px-4 py-3">Type</th><th class="px-4 py-3">Origine</th><th class="px-4 py-3">Projet</th><th class="px-4 py-3">Date</th><th class="px-4 py-3 text-right">Montant</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($contributions as $c)
                    <tr>
                        <td class="px-4 py-3">{{ $types[$c->type] ?? $c->type }}</td>
                        <td class="px-4 py-3 font-medium text-jci-900">{{ $c->origine }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $c->projet?->titre ?: 'Global' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $c->date_contribution?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right">{{ $fmt($c->montant) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Aucune recette.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $contributions->links() }}</div>
@endsection