@extends('layouts.app')
@section('title', 'Formation')

@section('content')
@php
    $statutLabel = ['planifiee'=>'Planifiée','realisee'=>'Réalisée','annulee'=>'Annulée'];
@endphp

<div class="flex items-center justify-between mb-4">
    <a href="{{ route('formations.index') }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour aux formations</a>
    <div class="flex gap-2">
        <a href="{{ route('formations.presence', $formation) }}" target="_blank" class="text-sm border px-4 py-2 rounded-lg hover:bg-slate-50">🖨️ Liste de présence</a>
        <a href="{{ route('formations.edit', $formation) }}" class="text-sm border px-4 py-2 rounded-lg hover:bg-slate-50">Modifier</a>
        <form method="POST" action="{{ route('formations.destroy', $formation) }}" onsubmit="return confirm('Supprimer cette formation ?')">
            @csrf @method('DELETE')
            <button class="text-sm border border-red-200 text-red-600 px-4 py-2 rounded-lg hover:bg-red-50">Supprimer</button>
        </form>
    </div>
</div>

{{-- En-tête --}}
<div class="bg-white border rounded-xl p-6 mb-5">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-jci-900">{{ $formation->titre }}</h1>
            <p class="text-slate-500">{{ $formation->theme }}</p>
        </div>
        <span class="text-xs px-2 py-1 rounded bg-jci-100 text-jci-700">{{ $statutLabel[$formation->statut] ?? $formation->statut }}</span>
    </div>
    <div class="grid sm:grid-cols-3 gap-4 mt-5 text-sm">
        <div><div class="text-slate-400">Date</div><div class="font-medium">{{ $formation->date_formation?->format('d/m/Y H:i') ?: '—' }}</div></div>
        <div><div class="text-slate-400">Lieu</div><div class="font-medium">{{ $formation->lieu ?: '—' }}</div></div>
        <div><div class="text-slate-400">Formateur</div><div class="font-medium">{{ $formation->formateur?->nom ?: '—' }} @if($formation->formateur)<span class="text-xs text-slate-400">({{ $formation->formateur->type }})</span>@endif</div></div>
    </div>
    @if ($formation->objectifs)
        <div class="mt-4"><div class="text-slate-400 text-sm">Objectifs</div><p class="mt-1 text-slate-700">{{ $formation->objectifs }}</p></div>
    @endif
</div>

<div class="grid lg:grid-cols-2 gap-5">
    {{-- Pointage --}}
    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-jci-900 mb-1">Pointage des présences</h2>
        <p class="text-xs text-slate-400 mb-4">Cochez les postulants présents, puis enregistrez.</p>
        <form method="POST" action="{{ route('formations.pointage', $formation) }}">
            @csrf
            <div class="space-y-1 max-h-80 overflow-auto pr-1">
                @forelse ($eligibles as $p)
                    <label class="flex items-center gap-3 py-1.5 px-2 rounded hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="present[]" value="{{ $p->id }}"
                               @checked($presences[$p->id] ?? false)
                               class="rounded border-slate-300 text-jci-600 focus:ring-jci-600">
                        <span class="text-sm">{{ $p->nom_complet }}</span>
                        <span class="ml-auto text-xs text-slate-400">{{ ucfirst(str_replace('_',' ',$p->statut)) }}</span>
                    </label>
                @empty
                    <p class="text-sm text-slate-400">Aucun postulant éligible.</p>
                @endforelse
            </div>
            <button class="mt-4 bg-jci-600 text-white px-4 py-2 rounded-lg hover:bg-jci-700 text-sm">Enregistrer les présences</button>
        </form>
    </div>

    {{-- Rapport --}}
    <div class="bg-white border rounded-xl p-6">
        <h2 class="font-semibold text-jci-900 mb-1">Rapport de formation</h2>
        <p class="text-xs text-slate-400 mb-3">Les postulants présents sont injectés automatiquement dans le rapport.</p>

        <div class="mb-4">
            <div class="text-sm font-medium text-slate-600 mb-1">Postulants présents ({{ $formation->presents()->count() }})</div>
            @php $presents = $formation->presents()->get(); @endphp
            @if ($presents->count())
                <ul class="text-sm text-slate-700 list-disc pl-5 space-y-0.5">
                    @foreach ($presents as $p) <li>{{ $p->nom_complet }}</li> @endforeach
                </ul>
            @else
                <p class="text-sm text-slate-400 italic">Aucun présent enregistré pour l'instant.</p>
            @endif
        </div>

        <form method="POST" action="{{ route('formations.rapport', $formation) }}">
            @csrf
            <label class="block text-sm font-medium mb-1">Contenu du rapport</label>
            <textarea name="contenu" rows="6" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">{{ old('contenu', $formation->rapport?->contenu) }}</textarea>
            <button class="mt-3 bg-jci-900 text-white px-4 py-2 rounded-lg hover:bg-jci-700 text-sm">
                {{ $formation->rapport ? 'Mettre à jour le rapport' : 'Enregistrer le rapport' }}
            </button>
        </form>
    </div>
</div>
@endsection
