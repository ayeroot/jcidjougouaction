@extends('layouts.app')
@section('title', 'Nouveau compte')
@section('content')
<a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour aux comptes</a>
<h1 class="text-2xl font-bold text-jci-900 mt-2 mb-1">Créer un compte</h1>
<p class="text-slate-500 text-sm mb-6">Sélectionnez un membre existant et attribuez-lui un rôle. Ses informations sont reprises de sa fiche ; un email d'activation lui est envoyé pour définir son mot de passe.</p>

@if ($errors->any())
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc pl-5 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

@if ($membres->isEmpty())
    <div class="bg-white border rounded-xl p-6 text-slate-500">
        Tous les membres disposant d'une adresse email ont déjà un compte.
        <a href="{{ route('membres.index') }}" class="text-jci-600 hover:underline">Ajouter d'abord un membre</a>
        (avec une adresse email) si nécessaire.
    </div>
@else
<form method="POST" action="{{ route('admin.users.store') }}" class="bg-white border rounded-xl p-6">
    @csrf
    <div class="grid sm:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-medium mb-1">Membre *</label>
            <select name="membre_id" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
                <option value="">— Choisir un membre —</option>
                @foreach ($membres as $m)
                    <option value="{{ $m->id }}" @selected(old('membre_id')==$m->id)>{{ $m->nom_complet }} — {{ $m->email }}</option>
                @endforeach
            </select>
            <p class="text-xs text-slate-400 mt-1">Seuls les membres sans compte et avec un email sont listés.</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Rôle *</label>
            <select name="role" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
                <option value="">— Choisir un rôle —</option>
                @foreach ($roles as $val => $lbl)
                    <option value="{{ $val }}" @selected(old('role')===$val)>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <button class="mt-6 bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Créer et envoyer l'activation</button>
</form>
@endif
@endsection
