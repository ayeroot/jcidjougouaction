@extends('layouts.app')
@section('title', 'Cotisations')
@section('content')
@php $fmt = fn($n)=>number_format((float)$n,0,',',' ').' FCFA'; @endphp
<h1 class="text-2xl font-bold text-jci-900 mb-4">Finances / Trésorerie</h1>
@include('finances._tabs')

<div class="grid lg:grid-cols-3 gap-5">
    <div class="bg-white border rounded-xl p-6 h-fit">
        <h2 class="font-semibold text-jci-900 mb-1">Enregistrer un versement</h2>
        <p class="text-xs text-slate-400 mb-3">Paiement échelonné : enregistrez chaque tranche. Le membre devient « honorable » une fois {{ $fmt($attendu) }} atteints.</p>
        @role('tresorier')
        <form method="POST" action="{{ route('finances.cotisations.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Membre</label>
                <select name="membre_id" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
                    <option value="">— Choisir —</option>
                    @foreach ($membres as $m)<option value="{{ $m->id }}">{{ $m->nom_complet }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Montant de la tranche (FCFA)</label>
                <input type="number" name="montant" step="1" min="0" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Date</label>
                <input type="date" name="date_cotisation" value="{{ date('Y-m-d') }}" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <button class="w-full bg-jci-900 text-white py-2 rounded-lg hover:bg-jci-700 text-sm">Enregistrer le versement</button>
        </form>
        @else
            <p class="text-sm text-slate-500">Consultation seule : seul le Trésorier peut enregistrer des mouvements financiers.</p>
        @endrole
    </div>

    <div class="lg:col-span-2 space-y-5">
        {{-- Récapitulatif par membre (paiement échelonné) --}}
        <div class="bg-white border rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b font-semibold text-jci-900 text-sm">Récapitulatif par membre</div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-left">
                    <tr><th class="px-4 py-2">Membre</th><th class="px-4 py-2 text-right">Cotisé</th><th class="px-4 py-2 text-right">Reste</th><th class="px-4 py-2 text-center">État</th></tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($membres as $m)
                        @php $paye = $m->montantCotise(); $reste = max(0, $attendu - $paye); @endphp
                        <tr>
                            <td class="px-4 py-2">{{ $m->nom_complet }}</td>
                            <td class="px-4 py-2 text-right">{{ $fmt($paye) }}</td>
                            <td class="px-4 py-2 text-right {{ $reste > 0 ? 'text-red-600' : 'text-slate-400' }}">{{ $fmt($reste) }}</td>
                            <td class="px-4 py-2 text-center">
                                @if ($m->estHonorable())
                                    <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-700">Honorable</span>
                                @elseif ($paye > 0)
                                    <span class="text-xs px-2 py-1 rounded bg-amber-100 text-amber-700">En cours</span>
                                @else
                                    <span class="text-xs px-2 py-1 rounded bg-red-100 text-red-700">Non payé</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Historique des versements --}}
        <div class="bg-white border rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b font-semibold text-jci-900 text-sm">Derniers versements</div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-left">
                    <tr><th class="px-4 py-2">Membre</th><th class="px-4 py-2">Date</th><th class="px-4 py-2 text-right">Montant</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($cotisations as $c)
                        <tr>
                            <td class="px-4 py-2 font-medium text-jci-900">{{ $c->membre?->nom_complet }}</td>
                            <td class="px-4 py-2 text-slate-500">{{ $c->date_cotisation?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-right">{{ $fmt($c->montant) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">Aucun versement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $cotisations->links() }}</div>
    </div>
</div>
@endsection
