@extends('layouts.app')
@section('title', 'Dépenses')
@section('content')
@php $fmt = fn($n)=>number_format((float)$n,0,',',' ').' FCFA'; @endphp
<h1 class="text-2xl font-bold text-jci-900 mb-4">Finances / Trésorerie</h1>
@include('finances._tabs')

<div class="grid lg:grid-cols-3 gap-5">
    <div class="bg-white border rounded-xl p-6 h-fit">
        <h2 class="font-semibold text-jci-900 mb-3">Enregistrer une dépense</h2>
        <form method="POST" action="{{ route('finances.depenses.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Libellé</label>
                <input name="libelle" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Projet (facultatif = global)</label>
                <select name="projet_id" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
                    <option value="">— Global —</option>
                    @foreach ($projets as $p)<option value="{{ $p->id }}">{{ $p->titre }}</option>@endforeach
                </select>
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
    </div>

    <div class="bg-white border rounded-xl overflow-hidden lg:col-span-2">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 text-left">
                <tr><th class="px-4 py-3">Libellé</th><th class="px-4 py-3">Projet</th><th class="px-4 py-3">Date</th><th class="px-4 py-3 text-right">Montant</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($depenses as $d)
                    <tr>
                        <td class="px-4 py-3 font-medium text-jci-900">{{ $d->libelle }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $d->projet?->titre ?: 'Global' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $d->date_depense?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right text-red-600">{{ $fmt($d->montant) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Aucune dépense.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $depenses->links() }}</div>
@endsection
