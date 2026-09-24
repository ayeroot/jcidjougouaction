@extends('layouts.app')
@section('title', 'Dépenses')
@section('content')
@php
    $fmt = fn($n)=>number_format((float)$n,0,',',' ').' FCFA';
    $cats = \App\Models\Depense::CATEGORIES;
    $catBadge = ['projet'=>'bg-cyan-100 text-cyan-700','prestation'=>'bg-purple-100 text-purple-700','location'=>'bg-blue-100 text-blue-700','domaine'=>'bg-indigo-100 text-indigo-700','secretariat'=>'bg-amber-100 text-amber-700','fonctionnement'=>'bg-slate-100 text-slate-600','autre'=>'bg-slate-100 text-slate-600'];
@endphp
   <h1 class="text-2xl font-bold text-jci-900 mb-4">Finances / Trésorerie</h1>
@include('finances._tabs')

<div class="grid lg:grid-cols-3 gap-5">
    <div class="bg-white border rounded-xl p-6 h-fit">
        <h2 class="font-semibold text-jci-900 mb-3">Enregistrer une dépense</h2>
        @can('finances.gerer')
        <form method="POST" action="{{ route('finances.depenses.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Libellé</label>
                <input name="libelle" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
                        <div>
                <label class="block text-sm font-medium mb-1">Catégorie</label>
                <select name="categorie" id="cat-depense" required onchange="majProjetDepense()" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
                    @foreach ($cats as $v=>$l)<option value="{{ $v }}" @selected(old('categorie')===$v)>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div id="bloc-projet-depense">
                <label class="block text-sm font-medium mb-1">Projet <span id="projet-obligatoire" class="text-red-500 hidden">*</span></label>
                <select name="projet_id" id="projet-depense" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
                    <option value="">— Aucun (dépense hors projet) —</option>
                    @foreach ($projets as $p)<option value="{{ $p->id }}" @selected(old('projet_id')==$p->id)>{{ $p->titre }}</option>@endforeach
                </select>
                <p class="text-xs text-slate-400 mt-1">Obligatoire seulement pour une dépense de projet.</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Montant (FCFA)</label>
                <input type="number" name="montant" step="1" min="0" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Date</label>
                <input type="date" name="date_depense" value="{{ date('Y-m-d') }}" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <button class="w-full bg-jci-900 text-white py-2 rounded-lg hover:bg-jci-700 text-sm">Enregistrer</button>
        </form>
                 <script>
            function majProjetDepense() {
                const estProjet = document.getElementById('cat-depense').value === 'projet';
                document.getElementById('projet-depense').required = estProjet;
                document.getElementById('projet-obligatoire').classList.toggle('hidden', !estProjet);
            }
            majProjetDepense();
        </script>
        @else
            <p class="text-sm text-slate-500">Consultation seule : seul le Trésorier peut enregistrer des mouvements financiers.</p>
        @endcan
    </div>

    <div class="bg-white border rounded-xl overflow-hidden lg:col-span-2">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 text-left">
                <tr><th class="px-4 py-3">Libellé</th><th class="px-4 py-3">Catégorie</th><th class="px-4 py-3">Projet</th><th class="px-4 py-3">Date</th><th class="px-4 py-3 text-right">Montant</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($depenses as $d)
                    <tr>
                        <td class="px-4 py-3 font-medium text-jci-900">{{ $d->libelle }}</td>
                        <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded {{ $catBadge[$d->categorie] ?? 'bg-slate-100' }}">{{ $cats[$d->categorie] ?? $d->categorie }}</span></td>
                        <td class="px-4 py-3 text-slate-500">{{ $d->projet?->titre ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $d->date_depense?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right text-red-600">{{ $fmt($d->montant) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Aucune dépense.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $depenses->links() }}</div>
@endsection
